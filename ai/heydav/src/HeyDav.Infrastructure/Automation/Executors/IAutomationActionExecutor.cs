using HeyDav.Domain.Automation.Entities;
using HeyDav.Domain.Automation.ValueObjects;

namespace HeyDav.Infrastructure.Automation.Executors;

public interface IAutomationActionExecutor
{
    Task<AutomationActionResult> ExecuteActionAsync(
        AutomationAction action, 
        Dictionary<string, object> context, 
        CancellationToken cancellationToken = default);

    Task<AutomationActionTestResult> TestActionAsync(
        AutomationAction action, 
        Dictionary<string, object> context, 
        CancellationToken cancellationToken = default);
}

public class AutomationActionTestResult
{
    public bool Success { get; set; }
    public string Message { get; set; } = string.Empty;
    public Dictionary<string, object> Data { get; set; } = new();
}