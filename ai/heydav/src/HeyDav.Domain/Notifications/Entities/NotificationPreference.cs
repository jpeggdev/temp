using HeyDav.Domain.Common.Base;
using HeyDav.Domain.Notifications.Enums;
using HeyDav.Domain.Notifications.ValueObjects;

namespace HeyDav.Domain.Notifications.Entities;

public class NotificationPreference : BaseEntity
{
    public string UserId { get; private set; } = string.Empty;
    public NotificationType NotificationType { get; private set; }
    public NotificationChannel PreferredChannel { get; private set; }
    public bool IsEnabled { get; private set; } = true;
    
    public NotificationSchedulingPreference SchedulingPreference { get; private set; } = new();
    public DoNotDisturbSettings DoNotDisturbSettings { get; private set; } = new();
    
    public int? MaxDailyNotifications { get; private set; }
    public TimeSpan? MinimumInterval { get; private set; }
    public NotificationPriority MinimumPriority { get; private set; } = NotificationPriority.Low;
    
    public Dictionary<string, object> CustomSettings { get; private set; } = new();

    private NotificationPreference() { } // For EF Core

    public NotificationPreference(
        string userId,
        NotificationType notificationType,
        NotificationChannel preferredChannel,
        bool isEnabled = true)
    {
        UserId = userId ?? throw new ArgumentNullException(nameof(userId));
        NotificationType = notificationType;
        PreferredChannel = preferredChannel;
        IsEnabled = isEnabled;
    }

    public void UpdateChannel(NotificationChannel channel)
    {
        PreferredChannel = channel;
        UpdateTimestamp();
    }

    public void Enable()
    {
        IsEnabled = true;
        UpdateTimestamp();
    }

    public void Disable()
    {
        IsEnabled = false;
        UpdateTimestamp();
    }

    public void UpdateSchedulingPreference(NotificationSchedulingPreference schedulingPreference)
    {
        SchedulingPreference = schedulingPreference ?? throw new ArgumentNullException(nameof(schedulingPreference));
        UpdateTimestamp();
    }

    public void UpdateDoNotDisturbSettings(DoNotDisturbSettings doNotDisturbSettings)
    {
        DoNotDisturbSettings = doNotDisturbSettings ?? throw new ArgumentNullException(nameof(doNotDisturbSettings));
        UpdateTimestamp();
    }

    public void SetDailyLimit(int maxDailyNotifications)
    {
        MaxDailyNotifications = maxDailyNotifications > 0 ? maxDailyNotifications : null;
        UpdateTimestamp();
    }

    public void SetMinimumInterval(TimeSpan minimumInterval)
    {
        MinimumInterval = minimumInterval > TimeSpan.Zero ? minimumInterval : null;
        UpdateTimestamp();
    }

    public void SetMinimumPriority(NotificationPriority minimumPriority)
    {
        MinimumPriority = minimumPriority;
        UpdateTimestamp();
    }

    public void UpdateCustomSetting(string key, object value)
    {
        CustomSettings[key] = value;
        UpdateTimestamp();
    }

    public void RemoveCustomSetting(string key)
    {
        CustomSettings.Remove(key);
        UpdateTimestamp();
    }

    public bool ShouldReceiveNotification(NotificationPriority priority, DateTime scheduledTime)
    {
        if (!IsEnabled)
            return false;

        if (priority < MinimumPriority)
            return false;

        if (DoNotDisturbSettings.IsInDoNotDisturbPeriod(scheduledTime))
            return false;

        return true;
    }
}