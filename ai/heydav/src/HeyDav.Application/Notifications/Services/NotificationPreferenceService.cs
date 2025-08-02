using HeyDav.Application.Common.Interfaces;
using HeyDav.Domain.Notifications.Entities;
using HeyDav.Domain.Notifications.Enums;
using HeyDav.Domain.Notifications.ValueObjects;
using Microsoft.EntityFrameworkCore;
using Microsoft.Extensions.Logging;

namespace HeyDav.Application.Notifications.Services;

public class NotificationPreferenceService : INotificationPreferenceService
{
    private readonly IApplicationDbContext _context;
    private readonly ILogger<NotificationPreferenceService> _logger;

    public NotificationPreferenceService(
        IApplicationDbContext context,
        ILogger<NotificationPreferenceService> logger)
    {
        _context = context ?? throw new ArgumentNullException(nameof(context));
        _logger = logger ?? throw new ArgumentNullException(nameof(logger));
    }

    public async Task<NotificationPreference?> GetPreferencesAsync(
        string userId, 
        NotificationType type, 
        CancellationToken cancellationToken = default)
    {
        try
        {
            var preference = await _context.NotificationPreferences
                .FirstOrDefaultAsync(p => p.UserId == userId && p.NotificationType == type, cancellationToken);

            if (preference == null)
            {
                // Create default preferences if none exist
                preference = await CreateDefaultPreferencesAsync(userId, type, cancellationToken);
            }

            return preference;
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to get notification preferences for user {UserId} and type {Type}", userId, type);
            return null;
        }
    }

    public async Task<List<NotificationPreference>> GetAllPreferencesAsync(
        string userId, 
        CancellationToken cancellationToken = default)
    {
        try
        {
            return await _context.NotificationPreferences
                .Where(p => p.UserId == userId)
                .ToListAsync(cancellationToken);
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to get all notification preferences for user {UserId}", userId);
            return new List<NotificationPreference>();
        }
    }

    public async Task<bool> UpdatePreferencesAsync(
        string userId,
        NotificationType type,
        NotificationChannel preferredChannel,
        bool isEnabled = true,
        CancellationToken cancellationToken = default)
    {
        try
        {
            var preference = await _context.NotificationPreferences
                .FirstOrDefaultAsync(p => p.UserId == userId && p.NotificationType == type, cancellationToken);

            if (preference == null)
            {
                preference = new NotificationPreference(userId, type, preferredChannel, isEnabled);
                _context.NotificationPreferences.Add(preference);
            }
            else
            {
                preference.UpdateChannel(preferredChannel);
                if (isEnabled)
                    preference.Enable();
                else
                    preference.Disable();
            }

            await _context.SaveChangesAsync(cancellationToken);
            _logger.LogInformation("Updated notification preferences for user {UserId} and type {Type}", userId, type);
            return true;
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to update notification preferences for user {UserId} and type {Type}", userId, type);
            return false;
        }
    }

    public async Task<bool> UpdateSchedulingPreferencesAsync(
        string userId,
        NotificationType type,
        NotificationSchedulingPreference schedulingPreference,
        CancellationToken cancellationToken = default)
    {
        try
        {
            var preference = await GetOrCreatePreferenceAsync(userId, type, cancellationToken);
            preference.UpdateSchedulingPreference(schedulingPreference);

            await _context.SaveChangesAsync(cancellationToken);
            _logger.LogInformation("Updated scheduling preferences for user {UserId} and type {Type}", userId, type);
            return true;
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to update scheduling preferences for user {UserId} and type {Type}", userId, type);
            return false;
        }
    }

    public async Task<bool> UpdateDoNotDisturbSettingsAsync(
        string userId,
        NotificationType type,
        DoNotDisturbSettings doNotDisturbSettings,
        CancellationToken cancellationToken = default)
    {
        try
        {
            var preference = await GetOrCreatePreferenceAsync(userId, type, cancellationToken);
            preference.UpdateDoNotDisturbSettings(doNotDisturbSettings);

            await _context.SaveChangesAsync(cancellationToken);
            _logger.LogInformation("Updated do-not-disturb settings for user {UserId} and type {Type}", userId, type);
            return true;
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to update do-not-disturb settings for user {UserId} and type {Type}", userId, type);
            return false;
        }
    }

    public async Task<bool> SetDailyLimitAsync(
        string userId,
        NotificationType type,
        int maxDailyNotifications,
        CancellationToken cancellationToken = default)
    {
        try
        {
            var preference = await GetOrCreatePreferenceAsync(userId, type, cancellationToken);
            preference.SetDailyLimit(maxDailyNotifications);

            await _context.SaveChangesAsync(cancellationToken);
            _logger.LogInformation("Set daily limit to {Limit} for user {UserId} and type {Type}", 
                maxDailyNotifications, userId, type);
            return true;
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to set daily limit for user {UserId} and type {Type}", userId, type);
            return false;
        }
    }

    public async Task<bool> SetMinimumIntervalAsync(
        string userId,
        NotificationType type,
        TimeSpan minimumInterval,
        CancellationToken cancellationToken = default)
    {
        try
        {
            var preference = await GetOrCreatePreferenceAsync(userId, type, cancellationToken);
            preference.SetMinimumInterval(minimumInterval);

            await _context.SaveChangesAsync(cancellationToken);
            _logger.LogInformation("Set minimum interval to {Interval} for user {UserId} and type {Type}", 
                minimumInterval, userId, type);
            return true;
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to set minimum interval for user {UserId} and type {Type}", userId, type);
            return false;
        }
    }

    public async Task<bool> SetMinimumPriorityAsync(
        string userId,
        NotificationType type,
        NotificationPriority minimumPriority,
        CancellationToken cancellationToken = default)
    {
        try
        {
            var preference = await GetOrCreatePreferenceAsync(userId, type, cancellationToken);
            preference.SetMinimumPriority(minimumPriority);

            await _context.SaveChangesAsync(cancellationToken);
            _logger.LogInformation("Set minimum priority to {Priority} for user {UserId} and type {Type}", 
                minimumPriority, userId, type);
            return true;
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to set minimum priority for user {UserId} and type {Type}", userId, type);
            return false;
        }
    }

    public async Task<NotificationPreference> CreateDefaultPreferencesAsync(
        string userId,
        NotificationType type,
        CancellationToken cancellationToken = default)
    {
        try
        {
            var defaultChannel = GetDefaultChannelForType(type);
            var preference = new NotificationPreference(userId, type, defaultChannel);

            // Set up default scheduling preferences
            var schedulingPreference = GetDefaultSchedulingPreference(type);
            preference.UpdateSchedulingPreference(schedulingPreference);

            // Set up default do-not-disturb settings
            var dndSettings = GetDefaultDoNotDisturbSettings();
            preference.UpdateDoNotDisturbSettings(dndSettings);

            _context.NotificationPreferences.Add(preference);
            await _context.SaveChangesAsync(cancellationToken);

            _logger.LogInformation("Created default preferences for user {UserId} and type {Type}", userId, type);
            return preference;
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to create default preferences for user {UserId} and type {Type}", userId, type);
            throw;
        }
    }

    public async Task<bool> ResetToDefaultsAsync(
        string userId,
        NotificationType? type = null,
        CancellationToken cancellationToken = default)
    {
        try
        {
            IQueryable<NotificationPreference> query = _context.NotificationPreferences
                .Where(p => p.UserId == userId);

            if (type.HasValue)
            {
                query = query.Where(p => p.NotificationType == type.Value);
            }

            var preferences = await query.ToListAsync(cancellationToken);

            foreach (var preference in preferences)
            {
                preference.MarkAsDeleted();
            }

            await _context.SaveChangesAsync(cancellationToken);

            _logger.LogInformation("Reset preferences to defaults for user {UserId} and type {Type}", userId, type);
            return true;
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to reset preferences for user {UserId} and type {Type}", userId, type);
            return false;
        }
    }

    public async Task<Dictionary<NotificationType, bool>> GetEnabledTypesAsync(
        string userId,
        CancellationToken cancellationToken = default)
    {
        try
        {
            var preferences = await GetAllPreferencesAsync(userId, cancellationToken);
            
            var result = new Dictionary<NotificationType, bool>();
            foreach (NotificationType notificationType in Enum.GetValues<NotificationType>())
            {
                var preference = preferences.FirstOrDefault(p => p.NotificationType == notificationType);
                result[notificationType] = preference?.IsEnabled ?? true; // Default to enabled
            }

            return result;
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to get enabled types for user {UserId}", userId);
            return new Dictionary<NotificationType, bool>();
        }
    }

    public async Task<Dictionary<NotificationType, NotificationChannel>> GetPreferredChannelsAsync(
        string userId,
        CancellationToken cancellationToken = default)
    {
        try
        {
            var preferences = await GetAllPreferencesAsync(userId, cancellationToken);
            
            var result = new Dictionary<NotificationType, NotificationChannel>();
            foreach (NotificationType notificationType in Enum.GetValues<NotificationType>())
            {
                var preference = preferences.FirstOrDefault(p => p.NotificationType == notificationType);
                result[notificationType] = preference?.PreferredChannel ?? GetDefaultChannelForType(notificationType);
            }

            return result;
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to get preferred channels for user {UserId}", userId);
            return new Dictionary<NotificationType, NotificationChannel>();
        }
    }

    private async Task<NotificationPreference> GetOrCreatePreferenceAsync(
        string userId, 
        NotificationType type, 
        CancellationToken cancellationToken)
    {
        var preference = await _context.NotificationPreferences
            .FirstOrDefaultAsync(p => p.UserId == userId && p.NotificationType == type, cancellationToken);

        if (preference == null)
        {
            preference = await CreateDefaultPreferencesAsync(userId, type, cancellationToken);
        }

        return preference;
    }

    private static NotificationChannel GetDefaultChannelForType(NotificationType type)
    {
        return type switch
        {
            NotificationType.SecurityAlert => NotificationChannel.Push,
            NotificationType.SystemAlert => NotificationChannel.Push,
            NotificationType.TaskDeadline => NotificationChannel.Push,
            NotificationType.MeetingReminder => NotificationChannel.Push,
            NotificationType.EmailDigest => NotificationChannel.Email,
            NotificationType.NewsUpdate => NotificationChannel.InApp,
            NotificationType.HabitReminder => NotificationChannel.Push,
            _ => NotificationChannel.InApp
        };
    }

    private static NotificationSchedulingPreference GetDefaultSchedulingPreference(NotificationType type)
    {
        return type switch
        {
            NotificationType.EmailDigest => new NotificationSchedulingPreference()
                .WithBatchingStrategy(NotificationBatchingStrategy.TimeWindow, TimeSpan.FromHours(1))
                .WithPreferredTimes(new TimeOnly(8, 0), new TimeOnly(18, 0)),

            NotificationType.NewsUpdate => new NotificationSchedulingPreference()
                .WithBatchingStrategy(NotificationBatchingStrategy.Count, maxSize: 5)
                .WithPreferredTimes(new TimeOnly(9, 0), new TimeOnly(17, 0)),

            NotificationType.HabitReminder => new NotificationSchedulingPreference()
                .WithGroupingStrategy(NotificationGroupingStrategy.ByType)
                .WithDelayConstraints(TimeSpan.FromMinutes(15), TimeSpan.FromHours(2)),

            _ => new NotificationSchedulingPreference()
        };
    }

    private static DoNotDisturbSettings GetDefaultDoNotDisturbSettings()
    {
        return new DoNotDisturbSettings()
            .WithSleepMode(new TimeOnly(22, 0), new TimeOnly(7, 0))
            .AllowPriority(NotificationPriority.Critical)
            .AllowPriority(NotificationPriority.Urgent);
    }
}