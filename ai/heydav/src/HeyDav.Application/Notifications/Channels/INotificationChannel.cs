using HeyDav.Application.Notifications.Services;
using HeyDav.Domain.Notifications.Entities;

namespace HeyDav.Application.Notifications.Channels;

public interface INotificationChannel
{
    string Name { get; }
    bool IsEnabled { get; }
    
    Task<NotificationDeliveryResult> SendAsync(Notification notification, CancellationToken cancellationToken = default);
    Task<bool> TestAsync(CancellationToken cancellationToken = default);
    Task<Dictionary<string, object>> GetStatusAsync(CancellationToken cancellationToken = default);
    Task<bool> ConfigureAsync(Dictionary<string, object> configuration, CancellationToken cancellationToken = default);
}

public interface INotificationChannelManager
{
    INotificationChannel GetChannel(Domain.Notifications.Enums.NotificationChannel channelType);
    List<INotificationChannel> GetAvailableChannels();
    Task<bool> RegisterChannelAsync(Domain.Notifications.Enums.NotificationChannel channelType, INotificationChannel channel);
    Task<Dictionary<Domain.Notifications.Enums.NotificationChannel, bool>> TestAllChannelsAsync(CancellationToken cancellationToken = default);
}