using HeyDav.Domain.Common.Base;
using HeyDav.Domain.Automation.Enums;

namespace HeyDav.Domain.Automation.ValueObjects;

public class AutomationTrigger : ValueObject
{
    public Guid Id { get; private set; } = Guid.NewGuid();
    public AutomationTriggerType Type { get; private set; }
    public string Name { get; private set; } = string.Empty;
    public string Description { get; private set; } = string.Empty;
    public Dictionary<string, object> Configuration { get; private set; } = new();
    public bool IsEnabled { get; private set; } = true;
    public int Order { get; private set; } = 0;

    private AutomationTrigger() { } // For EF Core

    public AutomationTrigger(
        AutomationTriggerType type,
        string name,
        string description = "",
        Dictionary<string, object>? configuration = null,
        int order = 0)
    {
        Type = type;
        Name = name ?? throw new ArgumentNullException(nameof(name));
        Description = description;
        Configuration = configuration ?? new Dictionary<string, object>();
        Order = order;
    }

    public AutomationTrigger WithConfiguration(string key, object value)
    {
        var newConfiguration = new Dictionary<string, object>(Configuration)
        {
            [key] = value
        };

        return new AutomationTrigger(Type, Name, Description, newConfiguration, Order);
    }

    public AutomationTrigger WithOrder(int order)
    {
        return new AutomationTrigger(Type, Name, Description, Configuration, order);
    }

    public AutomationTrigger Disable()
    {
        return new AutomationTrigger(Type, Name, Description, Configuration, Order)
        {
            IsEnabled = false
        };
    }

    public AutomationTrigger Enable()
    {
        return new AutomationTrigger(Type, Name, Description, Configuration, Order)
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

    // Factory methods for common trigger types
    public static AutomationTrigger CreateTimeTrigger(string name, DateTime scheduledTime, string? description = null)
    {
        var config = new Dictionary<string, object>
        {
            ["scheduledTime"] = scheduledTime,
            ["timeZone"] = TimeZoneInfo.Local.Id
        };

        return new AutomationTrigger(AutomationTriggerType.Time, name, description ?? "Time-based trigger", config);
    }

    public static AutomationTrigger CreateEventTrigger(string name, string eventType, string? description = null)
    {
        var config = new Dictionary<string, object>
        {
            ["eventType"] = eventType
        };

        return new AutomationTrigger(AutomationTriggerType.Event, name, description ?? "Event-based trigger", config);
    }

    public static AutomationTrigger CreateWebhookTrigger(string name, string endpoint, string? description = null)
    {
        var config = new Dictionary<string, object>
        {
            ["endpoint"] = endpoint,
            ["method"] = "POST"
        };

        return new AutomationTrigger(AutomationTriggerType.Webhook, name, description ?? "Webhook trigger", config);
    }

    public static AutomationTrigger CreateManualTrigger(string name, string? description = null)
    {
        return new AutomationTrigger(AutomationTriggerType.Manual, name, description ?? "Manual trigger");
    }

    protected override IEnumerable<object> GetEqualityComponents()
    {
        yield return Id;
        yield return Type;
        yield return Name;
        yield return Description;
        yield return IsEnabled;
        yield return Order;

        foreach (var kvp in Configuration.OrderBy(kvp => kvp.Key))
        {
            yield return kvp.Key;
            yield return kvp.Value;
        }
    }
}