using HeyDav.Domain.Notifications.Enums;

namespace HeyDav.Application.Notifications.Services;

public interface IAlertManager
{
    Task<Guid> CreateAlertAsync(
        string title,
        string description,
        AlertSeverity severity,
        string source,
        Dictionary<string, object>? metadata = null,
        List<string>? tags = null,
        string? assignedTo = null,
        CancellationToken cancellationToken = default);

    Task<bool> AcknowledgeAlertAsync(
        Guid alertId,
        string acknowledgedBy,
        string? notes = null,
        CancellationToken cancellationToken = default);

    Task<bool> ResolveAlertAsync(
        Guid alertId,
        string resolvedBy,
        string resolution,
        CancellationToken cancellationToken = default);

    Task<bool> EscalateAlertAsync(
        Guid alertId,
        string escalatedTo,
        string reason,
        CancellationToken cancellationToken = default);

    Task<bool> AssignAlertAsync(
        Guid alertId,
        string assignedTo,
        string? assignedBy = null,
        CancellationToken cancellationToken = default);

    Task<List<Alert>> GetActiveAlertsAsync(
        AlertSeverity? severity = null,
        string? source = null,
        string? assignedTo = null,
        CancellationToken cancellationToken = default);

    Task<List<Alert>> GetAlertHistoryAsync(
        DateTime? fromDate = null,
        DateTime? toDate = null,
        int skip = 0,
        int take = 50,
        CancellationToken cancellationToken = default);

    Task<Alert?> GetAlertAsync(Guid alertId, CancellationToken cancellationToken = default);

    Task<bool> CorrelateAlertsAsync(
        List<Guid> alertIds,
        string correlationReason,
        CancellationToken cancellationToken = default);

    Task<bool> DeduplicateAlertsAsync(
        string source,
        string fingerprint,
        TimeSpan windowSize,
        CancellationToken cancellationToken = default);

    Task<AlertRule> CreateAlertRuleAsync(
        string name,
        string description,
        AlertCondition condition,
        AlertAction action,
        AlertSeverity severity,
        TimeSpan? suppressionWindow = null,
        CancellationToken cancellationToken = default);

    Task<bool> UpdateAlertRuleAsync(
        Guid ruleId,
        string? name = null,
        string? description = null,
        AlertCondition? condition = null,
        AlertAction? action = null,
        AlertSeverity? severity = null,
        TimeSpan? suppressionWindow = null,
        CancellationToken cancellationToken = default);

    Task<bool> EnableAlertRuleAsync(Guid ruleId, CancellationToken cancellationToken = default);
    Task<bool> DisableAlertRuleAsync(Guid ruleId, CancellationToken cancellationToken = default);

    Task<List<AlertRule>> GetAlertRulesAsync(bool includeDisabled = false, CancellationToken cancellationToken = default);

    Task ProcessAlertRulesAsync(CancellationToken cancellationToken = default);
    Task ProcessEscalationPoliciesAsync(CancellationToken cancellationToken = default);
    Task CleanupResolvedAlertsAsync(TimeSpan maxAge, CancellationToken cancellationToken = default);

    Task<AlertStatistics> GetAlertStatisticsAsync(
        DateTime? fromDate = null,
        DateTime? toDate = null,
        CancellationToken cancellationToken = default);
}

public class Alert
{
    public Guid Id { get; set; }
    public string Title { get; set; } = string.Empty;
    public string Description { get; set; } = string.Empty;
    public AlertSeverity Severity { get; set; }
    public AlertStatus Status { get; set; }
    public string Source { get; set; } = string.Empty;
    public DateTime CreatedAt { get; set; }
    public DateTime? AcknowledgedAt { get; set; }
    public DateTime? ResolvedAt { get; set; }
    public string? AcknowledgedBy { get; set; }
    public string? ResolvedBy { get; set; }
    public string? AssignedTo { get; set; }
    public string? Resolution { get; set; }
    public Dictionary<string, object> Metadata { get; set; } = new();
    public List<string> Tags { get; set; } = new();
    public List<AlertEscalation> Escalations { get; set; } = new();
    public List<AlertComment> Comments { get; set; } = new();
    public string? Fingerprint { get; set; }
    public Guid? CorrelationId { get; set; }
    public int EscalationLevel { get; set; } = 0;
}

public class AlertRule
{
    public Guid Id { get; set; }
    public string Name { get; set; } = string.Empty;
    public string Description { get; set; } = string.Empty;
    public AlertCondition Condition { get; set; } = new();
    public AlertAction Action { get; set; } = new();
    public AlertSeverity Severity { get; set; }
    public bool IsEnabled { get; set; } = true;
    public TimeSpan? SuppressionWindow { get; set; }
    public DateTime? LastTriggered { get; set; }
    public int TriggerCount { get; set; } = 0;
}

public class AlertCondition
{
    public string MetricName { get; set; } = string.Empty;
    public string Operator { get; set; } = string.Empty;
    public double Threshold { get; set; }
    public TimeSpan Duration { get; set; }
    public Dictionary<string, object> Parameters { get; set; } = new();
}

public class AlertAction
{
    public AlertActionType Type { get; set; }
    public Dictionary<string, object> Configuration { get; set; } = new();
    public List<string> Recipients { get; set; } = new();
}

public class AlertEscalation
{
    public Guid Id { get; set; }
    public DateTime EscalatedAt { get; set; }
    public string EscalatedTo { get; set; } = string.Empty;
    public string Reason { get; set; } = string.Empty;
    public int Level { get; set; }
}

public class AlertComment
{
    public Guid Id { get; set; }
    public string Author { get; set; } = string.Empty;
    public string Content { get; set; } = string.Empty;
    public DateTime CreatedAt { get; set; }
}

public class AlertStatistics
{
    public int TotalAlerts { get; set; }
    public int ActiveAlerts { get; set; }
    public int AcknowledgedAlerts { get; set; }
    public int ResolvedAlerts { get; set; }
    public Dictionary<AlertSeverity, int> AlertsBySeverity { get; set; } = new();
    public Dictionary<string, int> AlertsBySource { get; set; } = new();
    public TimeSpan AverageResolutionTime { get; set; }
    public TimeSpan AverageAcknowledgmentTime { get; set; }
}

public enum AlertSeverity
{
    Info = 0,
    Warning = 1,
    Error = 2,
    Critical = 3,
    Fatal = 4
}

public enum AlertStatus
{
    Open = 0,
    Acknowledged = 1,
    Resolved = 2,
    Closed = 3,
    Suppressed = 4
}

public enum AlertActionType
{
    SendNotification = 0,
    SendEmail = 1,
    SendSMS = 2,
    CallWebhook = 3,
    CreateTicket = 4,
    RunScript = 5
}