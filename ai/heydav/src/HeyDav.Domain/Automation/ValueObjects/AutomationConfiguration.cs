using HeyDav.Domain.Common.Base;
using HeyDav.Domain.Automation.Enums;

namespace HeyDav.Domain.Automation.ValueObjects;

public class AutomationConfiguration : ValueObject
{
    public AutomationPriority Priority { get; private set; } = AutomationPriority.Normal;
    public AutomationRunMode RunMode { get; private set; } = AutomationRunMode.Sequential;
    public TimeSpan? Timeout { get; private set; }
    public int MaxConcurrentExecutions { get; private set; } = 1;
    public bool AllowManualTrigger { get; private set; } = true;
    public bool LogExecution { get; private set; } = true;
    public AutomationLogLevel LogLevel { get; private set; } = AutomationLogLevel.Information;
    public bool NotifyOnSuccess { get; private set; } = false;
    public bool NotifyOnFailure { get; private set; } = true;
    public string? NotificationRecipient { get; private set; }
    public Dictionary<string, object> CustomSettings { get; private set; } = new();
    public List<string> RequiredPermissions { get; private set; } = new();
    public Dictionary<string, string> EnvironmentVariables { get; private set; } = new();

    public AutomationConfiguration() { }

    public AutomationConfiguration(
        AutomationPriority priority = AutomationPriority.Normal,
        AutomationRunMode runMode = AutomationRunMode.Sequential,
        TimeSpan? timeout = null,
        int maxConcurrentExecutions = 1,
        bool allowManualTrigger = true,
        bool logExecution = true,
        AutomationLogLevel logLevel = AutomationLogLevel.Information,
        bool notifyOnSuccess = false,
        bool notifyOnFailure = true,
        string? notificationRecipient = null,
        Dictionary<string, object>? customSettings = null,
        List<string>? requiredPermissions = null,
        Dictionary<string, string>? environmentVariables = null)
    {
        Priority = priority;
        RunMode = runMode;
        Timeout = timeout;
        MaxConcurrentExecutions = maxConcurrentExecutions;
        AllowManualTrigger = allowManualTrigger;
        LogExecution = logExecution;
        LogLevel = logLevel;
        NotifyOnSuccess = notifyOnSuccess;
        NotifyOnFailure = notifyOnFailure;
        NotificationRecipient = notificationRecipient;
        CustomSettings = customSettings ?? new Dictionary<string, object>();
        RequiredPermissions = requiredPermissions ?? new List<string>();
        EnvironmentVariables = environmentVariables ?? new Dictionary<string, string>();
    }

    public AutomationConfiguration WithPriority(AutomationPriority priority)
    {
        return new AutomationConfiguration(
            priority, RunMode, Timeout, MaxConcurrentExecutions, AllowManualTrigger,
            LogExecution, LogLevel, NotifyOnSuccess, NotifyOnFailure, NotificationRecipient,
            CustomSettings, RequiredPermissions, EnvironmentVariables);
    }

    public AutomationConfiguration WithRunMode(AutomationRunMode runMode)
    {
        return new AutomationConfiguration(
            Priority, runMode, Timeout, MaxConcurrentExecutions, AllowManualTrigger,
            LogExecution, LogLevel, NotifyOnSuccess, NotifyOnFailure, NotificationRecipient,
            CustomSettings, RequiredPermissions, EnvironmentVariables);
    }

    public AutomationConfiguration WithTimeout(TimeSpan timeout)
    {
        return new AutomationConfiguration(
            Priority, RunMode, timeout, MaxConcurrentExecutions, AllowManualTrigger,
            LogExecution, LogLevel, NotifyOnSuccess, NotifyOnFailure, NotificationRecipient,
            CustomSettings, RequiredPermissions, EnvironmentVariables);
    }

    public AutomationConfiguration WithConcurrency(int maxConcurrentExecutions)
    {
        return new AutomationConfiguration(
            Priority, RunMode, Timeout, maxConcurrentExecutions, AllowManualTrigger,
            LogExecution, LogLevel, NotifyOnSuccess, NotifyOnFailure, NotificationRecipient,
            CustomSettings, RequiredPermissions, EnvironmentVariables);
    }

    public AutomationConfiguration WithNotifications(bool notifyOnSuccess, bool notifyOnFailure, string? recipient = null)
    {
        return new AutomationConfiguration(
            Priority, RunMode, Timeout, MaxConcurrentExecutions, AllowManualTrigger,
            LogExecution, LogLevel, notifyOnSuccess, notifyOnFailure, recipient,
            CustomSettings, RequiredPermissions, EnvironmentVariables);
    }

    public AutomationConfiguration WithLogging(bool logExecution, AutomationLogLevel logLevel = AutomationLogLevel.Information)
    {
        return new AutomationConfiguration(
            Priority, RunMode, Timeout, MaxConcurrentExecutions, AllowManualTrigger,
            logExecution, logLevel, NotifyOnSuccess, NotifyOnFailure, NotificationRecipient,
            CustomSettings, RequiredPermissions, EnvironmentVariables);
    }

    public AutomationConfiguration WithManualTrigger(bool allowManualTrigger)
    {
        return new AutomationConfiguration(
            Priority, RunMode, Timeout, MaxConcurrentExecutions, allowManualTrigger,
            LogExecution, LogLevel, NotifyOnSuccess, NotifyOnFailure, NotificationRecipient,
            CustomSettings, RequiredPermissions, EnvironmentVariables);
    }

    public AutomationConfiguration WithCustomSetting(string key, object value)
    {
        var newCustomSettings = new Dictionary<string, object>(CustomSettings)
        {
            [key] = value
        };

        return new AutomationConfiguration(
            Priority, RunMode, Timeout, MaxConcurrentExecutions, AllowManualTrigger,
            LogExecution, LogLevel, NotifyOnSuccess, NotifyOnFailure, NotificationRecipient,
            newCustomSettings, RequiredPermissions, EnvironmentVariables);
    }

    public AutomationConfiguration WithPermission(string permission)
    {
        var newPermissions = new List<string>(RequiredPermissions);
        if (!newPermissions.Contains(permission))
        {
            newPermissions.Add(permission);
        }

        return new AutomationConfiguration(
            Priority, RunMode, Timeout, MaxConcurrentExecutions, AllowManualTrigger,
            LogExecution, LogLevel, NotifyOnSuccess, NotifyOnFailure, NotificationRecipient,
            CustomSettings, newPermissions, EnvironmentVariables);
    }

    public AutomationConfiguration WithEnvironmentVariable(string key, string value)
    {
        var newEnvironmentVariables = new Dictionary<string, string>(EnvironmentVariables)
        {
            [key] = value
        };

        return new AutomationConfiguration(
            Priority, RunMode, Timeout, MaxConcurrentExecutions, AllowManualTrigger,
            LogExecution, LogLevel, NotifyOnSuccess, NotifyOnFailure, NotificationRecipient,
            CustomSettings, RequiredPermissions, newEnvironmentVariables);
    }

    public T? GetCustomSetting<T>(string key, T? defaultValue = default)
    {
        if (CustomSettings.TryGetValue(key, out var value))
        {
            try
            {
                return (T)Convert.ChangeType(value, typeof(T));
            }
            catch
            {
                return defaultValue;
            }
        }
        return defaultValue;
    }

    public bool HasPermission(string permission)
    {
        return RequiredPermissions.Contains(permission, StringComparer.OrdinalIgnoreCase);
    }

    public string? GetEnvironmentVariable(string key)
    {
        return EnvironmentVariables.TryGetValue(key, out var value) ? value : null;
    }

    protected override IEnumerable<object> GetEqualityComponents()
    {
        yield return Priority;
        yield return RunMode;
        yield return Timeout?.ToString() ?? string.Empty;
        yield return MaxConcurrentExecutions;
        yield return AllowManualTrigger;
        yield return LogExecution;
        yield return LogLevel;
        yield return NotifyOnSuccess;
        yield return NotifyOnFailure;
        yield return NotificationRecipient ?? string.Empty;

        foreach (var kvp in CustomSettings.OrderBy(kvp => kvp.Key))
        {
            yield return kvp.Key;
            yield return kvp.Value;
        }

        foreach (var permission in RequiredPermissions.OrderBy(p => p))
        {
            yield return permission;
        }

        foreach (var kvp in EnvironmentVariables.OrderBy(kvp => kvp.Key))
        {
            yield return kvp.Key;
            yield return kvp.Value;
        }
    }
}