using HeyDav.Application.Common.Interfaces;
using HeyDav.Application.Notifications.Services;
using HeyDav.Domain.Notifications.Entities;
using Microsoft.Extensions.Logging;

namespace HeyDav.Application.Notifications.Channels;

public class InAppNotificationChannel : INotificationChannel
{
    private readonly IApplicationDbContext _context;
    private readonly ILogger<InAppNotificationChannel> _logger;

    public string Name => "In-App";
    public bool IsEnabled => true;

    public InAppNotificationChannel(
        IApplicationDbContext context,
        ILogger<InAppNotificationChannel> logger)
    {
        _context = context ?? throw new ArgumentNullException(nameof(context));
        _logger = logger ?? throw new ArgumentNullException(nameof(logger));
    }

    public async Task<NotificationDeliveryResult> SendAsync(Notification notification, CancellationToken cancellationToken = default)
    {
        try
        {
            // For in-app notifications, we just mark them as delivered
            // The actual display is handled by the UI layer
            _logger.LogDebug("Delivered in-app notification {NotificationId} to recipient {RecipientId}",
                notification.Id, notification.RecipientId);

            return new NotificationDeliveryResult(true, metadata: new Dictionary<string, object>
            {
                ["channel"] = "InApp",
                ["deliveredAt"] = DateTime.UtcNow
            });
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to deliver in-app notification {NotificationId}", notification.Id);
            return new NotificationDeliveryResult(false, ex.Message);
        }
    }

    public Task<bool> TestAsync(CancellationToken cancellationToken = default)
    {
        // In-app notifications are always available
        return Task.FromResult(true);
    }

    public Task<Dictionary<string, object>> GetStatusAsync(CancellationToken cancellationToken = default)
    {
        return Task.FromResult(new Dictionary<string, object>
        {
            ["status"] = "healthy",
            ["enabled"] = IsEnabled,
            ["lastCheck"] = DateTime.UtcNow
        });
    }

    public Task<bool> ConfigureAsync(Dictionary<string, object> configuration, CancellationToken cancellationToken = default)
    {
        // In-app notifications don't require configuration
        return Task.FromResult(true);
    }
}