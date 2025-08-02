using HeyDav.Domain.Notifications.Entities;
using HeyDav.Domain.Notifications.Enums;
using HeyDav.Domain.Notifications.ValueObjects;

namespace HeyDav.Application.Notifications.Services;

public interface INotificationPreferenceService
{
    Task<NotificationPreference?> GetPreferencesAsync(
        string userId, 
        NotificationType type, 
        CancellationToken cancellationToken = default);

    Task<List<NotificationPreference>> GetAllPreferencesAsync(
        string userId, 
        CancellationToken cancellationToken = default);

    Task<bool> UpdatePreferencesAsync(
        string userId,
        NotificationType type,
        NotificationChannel preferredChannel,
        bool isEnabled = true,
        CancellationToken cancellationToken = default);

    Task<bool> UpdateSchedulingPreferencesAsync(
        string userId,
        NotificationType type,
        NotificationSchedulingPreference schedulingPreference,
        CancellationToken cancellationToken = default);

    Task<bool> UpdateDoNotDisturbSettingsAsync(
        string userId,
        NotificationType type,
        DoNotDisturbSettings doNotDisturbSettings,
        CancellationToken cancellationToken = default);

    Task<bool> SetDailyLimitAsync(
        string userId,
        NotificationType type,
        int maxDailyNotifications,
        CancellationToken cancellationToken = default);

    Task<bool> SetMinimumIntervalAsync(
        string userId,
        NotificationType type,
        TimeSpan minimumInterval,
        CancellationToken cancellationToken = default);

    Task<bool> SetMinimumPriorityAsync(
        string userId,
        NotificationType type,
        NotificationPriority minimumPriority,
        CancellationToken cancellationToken = default);

    Task<NotificationPreference> CreateDefaultPreferencesAsync(
        string userId,
        NotificationType type,
        CancellationToken cancellationToken = default);

    Task<bool> ResetToDefaultsAsync(
        string userId,
        NotificationType? type = null,
        CancellationToken cancellationToken = default);

    Task<Dictionary<NotificationType, bool>> GetEnabledTypesAsync(
        string userId,
        CancellationToken cancellationToken = default);

    Task<Dictionary<NotificationType, NotificationChannel>> GetPreferredChannelsAsync(
        string userId,
        CancellationToken cancellationToken = default);
}