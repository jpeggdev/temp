namespace HeyDav.Application.ToolIntegrations.Models;

public record WebhookStatistics(
    Guid? EndpointId,
    string? EndpointName,
    int TotalEvents,
    int SuccessfulEvents,
    int FailedEvents,
    double SuccessRate,
    DateTime? LastEventReceived,
    DateTime? LastSuccessfulEvent,
    Dictionary<string, int> EventTypeDistribution,
    Dictionary<string, object> CustomMetrics);

public record WebhookProcessingResult(
    bool IsSuccessful,
    string? ErrorMessage = null,
    List<string>? TriggeredWorkflows = null,
    Dictionary<string, object>? ProcessingMetadata = null,
    TimeSpan ProcessingTime = default);

public record WebhookRetryInfo(
    int RetryCount,
    DateTime? NextRetryAt,
    TimeSpan? BackoffDuration,
    string? LastError);

public record WebhookHealthStatus(
    Guid EndpointId,
    string EndpointName,
    bool IsHealthy,
    double SuccessRate,
    int RecentFailures,
    DateTime? LastSuccessfulEvent,
    List<string> HealthIssues);

public class WebhookValidationException : ToolIntegrationException
{
    public string ValidationError { get; }

    public WebhookValidationException(string validationError, string message)
        : base(message, "WEBHOOK_VALIDATION_FAILED")
    {
        ValidationError = validationError;
    }
}

public class WebhookProcessingException : ToolIntegrationException
{
    public Guid WebhookEventId { get; }

    public WebhookProcessingException(Guid webhookEventId, string message, Exception? innerException = null)
        : base(message, innerException, "WEBHOOK_PROCESSING_FAILED")
    {
        WebhookEventId = webhookEventId;
    }
}