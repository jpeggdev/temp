using HeyDav.Domain.Notifications.Enums;
using Microsoft.Extensions.Logging;

namespace HeyDav.Application.Notifications.Channels;

public class NotificationChannelManager : INotificationChannelManager
{
    private readonly Dictionary<NotificationChannel, INotificationChannel> _channels;
    private readonly ILogger<NotificationChannelManager> _logger;

    public NotificationChannelManager(
        InAppNotificationChannel inAppChannel,
        EmailNotificationChannel emailChannel,
        ILogger<NotificationChannelManager> logger)
    {
        _logger = logger ?? throw new ArgumentNullException(nameof(logger));
        
        _channels = new Dictionary<NotificationChannel, INotificationChannel>
        {
            { NotificationChannel.InApp, inAppChannel },
            { NotificationChannel.Email, emailChannel }
        };
    }

    public INotificationChannel GetChannel(NotificationChannel channelType)
    {
        if (_channels.TryGetValue(channelType, out var channel))
        {
            return channel;
        }

        _logger.LogWarning("Notification channel {ChannelType} not found, falling back to in-app", channelType);
        return _channels[NotificationChannel.InApp];
    }

    public List<INotificationChannel> GetAvailableChannels()
    {
        return _channels.Values.Where(c => c.IsEnabled).ToList();
    }

    public Task<bool> RegisterChannelAsync(NotificationChannel channelType, INotificationChannel channel)
    {
        try
        {
            _channels[channelType] = channel;
            _logger.LogInformation("Registered notification channel {ChannelType}: {ChannelName}", 
                channelType, channel.Name);
            return Task.FromResult(true);
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to register notification channel {ChannelType}", channelType);
            return Task.FromResult(false);
        }
    }

    public async Task<Dictionary<NotificationChannel, bool>> TestAllChannelsAsync(CancellationToken cancellationToken = default)
    {
        var results = new Dictionary<NotificationChannel, bool>();

        foreach (var kvp in _channels)
        {
            try
            {
                var testResult = await kvp.Value.TestAsync(cancellationToken);
                results[kvp.Key] = testResult;
                
                _logger.LogInformation("Channel {ChannelType} test result: {Result}", 
                    kvp.Key, testResult ? "Success" : "Failed");
            }
            catch (Exception ex)
            {
                _logger.LogError(ex, "Failed to test channel {ChannelType}", kvp.Key);
                results[kvp.Key] = false;
            }
        }

        return results;
    }
}