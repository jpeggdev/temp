using HeyDav.Domain.Common.Base;
using HeyDav.Domain.Automation.Enums;

namespace HeyDav.Domain.Automation.ValueObjects;

public class AutomationAction : ValueObject
{
    public Guid Id { get; private set; } = Guid.NewGuid();
    public AutomationActionType Type { get; private set; }
    public string Name { get; private set; } = string.Empty;
    public string Description { get; private set; } = string.Empty;
    public Dictionary<string, object> Configuration { get; private set; } = new();
    public bool IsEnabled { get; private set; } = true;
    public int Order { get; private set; } = 0;
    public bool ContinueOnError { get; private set; } = false;
    public TimeSpan? Timeout { get; private set; }
    public int RetryCount { get; private set; } = 0;
    public TimeSpan? RetryDelay { get; private set; }

    private AutomationAction() { } // For EF Core

    public AutomationAction(
        AutomationActionType type,
        string name,
        string description = "",
        Dictionary<string, object>? configuration = null,
        int order = 0,
        bool continueOnError = false,
        TimeSpan? timeout = null,
        int retryCount = 0,
        TimeSpan? retryDelay = null)
    {
        Type = type;
        Name = name ?? throw new ArgumentNullException(nameof(name));
        Description = description;
        Configuration = configuration ?? new Dictionary<string, object>();
        Order = order;
        ContinueOnError = continueOnError;
        Timeout = timeout;
        RetryCount = retryCount;
        RetryDelay = retryDelay;
    }

    public AutomationAction WithConfiguration(string key, object value)
    {
        var newConfiguration = new Dictionary<string, object>(Configuration)
        {
            [key] = value
        };

        return new AutomationAction(Type, Name, Description, newConfiguration, Order, ContinueOnError, Timeout, RetryCount, RetryDelay);
    }

    public AutomationAction WithOrder(int order)
    {
        return new AutomationAction(Type, Name, Description, Configuration, order, ContinueOnError, Timeout, RetryCount, RetryDelay);
    }

    public AutomationAction WithRetry(int retryCount, TimeSpan? retryDelay = null)
    {
        return new AutomationAction(Type, Name, Description, Configuration, Order, ContinueOnError, Timeout, retryCount, retryDelay);
    }

    public AutomationAction WithTimeout(TimeSpan timeout)
    {
        return new AutomationAction(Type, Name, Description, Configuration, Order, ContinueOnError, timeout, RetryCount, RetryDelay);
    }

    public AutomationAction WithContinueOnError(bool continueOnError = true)
    {
        return new AutomationAction(Type, Name, Description, Configuration, Order, continueOnError, Timeout, RetryCount, RetryDelay);
    }

    public AutomationAction Disable()
    {
        return new AutomationAction(Type, Name, Description, Configuration, Order, ContinueOnError, Timeout, RetryCount, RetryDelay)
        {
            IsEnabled = false
        };
    }

    public AutomationAction Enable()
    {
        return new AutomationAction(Type, Name, Description, Configuration, Order, ContinueOnError, Timeout, RetryCount, RetryDelay)
        {
            IsEnabled = true
        };
    }

    public T? GetConfigurationValue<T>(string key, T? defaultValue = default)
    {
        if (Configuration.TryGetValue(key, out var value))
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

    public bool HasConfiguration(string key)
    {
        return Configuration.ContainsKey(key);
    }

    // Factory methods for common action types
    public static AutomationAction CreateNotificationAction(string title, string content, string? recipient = null)
    {
        var config = new Dictionary<string, object>
        {
            ["title"] = title,
            ["content"] = content
        };

        if (!string.IsNullOrEmpty(recipient))
        {
            config["recipient"] = recipient;
        }

        return new AutomationAction(AutomationActionType.SendNotification, "Send Notification", "Send a notification", config);
    }

    public static AutomationAction CreateEmailAction(string to, string subject, string body, string? from = null)
    {
        var config = new Dictionary<string, object>
        {
            ["to"] = to,
            ["subject"] = subject,
            ["body"] = body
        };

        if (!string.IsNullOrEmpty(from))
        {
            config["from"] = from;
        }

        return new AutomationAction(AutomationActionType.SendEmail, "Send Email", "Send an email", config);
    }

    public static AutomationAction CreateTaskAction(string title, string? description = null, DateTime? dueDate = null)
    {
        var config = new Dictionary<string, object>
        {
            ["title"] = title
        };

        if (!string.IsNullOrEmpty(description))
        {
            config["description"] = description;
        }

        if (dueDate.HasValue)
        {
            config["dueDate"] = dueDate.Value;
        }

        return new AutomationAction(AutomationActionType.CreateTask, "Create Task", "Create a new task", config);
    }

    public static AutomationAction CreateWebhookAction(string url, string method = "POST", Dictionary<string, object>? payload = null)
    {
        var config = new Dictionary<string, object>
        {
            ["url"] = url,
            ["method"] = method
        };

        if (payload != null)
        {
            config["payload"] = payload;
        }

        return new AutomationAction(AutomationActionType.SendWebhook, "Send Webhook", "Send a webhook request", config);
    }

    public static AutomationAction CreateCustomAction(string name, string description, Dictionary<string, object> configuration)
    {
        return new AutomationAction(AutomationActionType.Custom, name, description, configuration);
    }

    protected override IEnumerable<object> GetEqualityComponents()
    {
        yield return Id;
        yield return Type;
        yield return Name;
        yield return Description;
        yield return IsEnabled;
        yield return Order;
        yield return ContinueOnError;
        yield return Timeout?.ToString() ?? string.Empty;
        yield return RetryCount;
        yield return RetryDelay?.ToString() ?? string.Empty;

        foreach (var kvp in Configuration.OrderBy(kvp => kvp.Key))
        {
            yield return kvp.Key;
            yield return kvp.Value;
        }
    }
}