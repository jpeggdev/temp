using HeyDav.Domain.Automation.ValueObjects;

namespace HeyDav.Infrastructure.Automation.Triggers;

public interface IAutomationTriggerManager
{
    Task RegisterTriggersAsync(Guid ruleId, List<AutomationTrigger> triggers, CancellationToken cancellationToken = default);
    Task UnregisterTriggersAsync(Guid ruleId, CancellationToken cancellationToken = default);
    Task ProcessTriggersAsync(CancellationToken cancellationToken = default);
    Task TriggerEventAsync(string eventType, Dictionary<string, object>? data = null, CancellationToken cancellationToken = default);
}