using HeyDav.Domain.Common.Base;
using HeyDav.Domain.ToolIntegrations.Enums;

namespace HeyDav.Domain.ToolIntegrations.Entities;

public class WebhookEvent : BaseEntity
{
    public Guid WebhookEndpointId { get; private set; }
    public string EventType { get; private set; } = string.Empty;
    public string EventId { get; private set; } = string.Empty;
    public string Payload { get; private set; } = string.Empty;
    public string? Headers { get; private set; }
    public string? Signature { get; private set; }
    public DateTime ReceivedAt { get; private set; }
    public DateTime? ProcessedAt { get; private set; }
    public WebhookEventStatus Status { get; private set; }
    public bool WasSuccessful { get; private set; }
    public string? ErrorMessage { get; private set; }
    public int RetryCount { get; private set; }
    public DateTime? NextRetryAt { get; private set; }
    
    // Processing metadata
    public string? ProcessingLog { get; private set; }
    public TimeSpan? ProcessingDuration { get; private set; }
    public string? TriggeredWorkflows { get; private set; }
    
    // Navigation properties
    public virtual WebhookEndpoint WebhookEndpoint { get; private set; } = null!;

    private WebhookEvent() { } // EF Core constructor

    public WebhookEvent(
        Guid webhookEndpointId,
        string eventType,
        string eventId,
        string payload,
        string? headers = null,
        string? signature = null)
    {
        WebhookEndpointId = webhookEndpointId;
        EventType = eventType ?? throw new ArgumentNullException(nameof(eventType));
        EventId = eventId ?? throw new ArgumentNullException(nameof(eventId));
        Payload = payload ?? throw new ArgumentNullException(nameof(payload));
        Headers = headers;
        Signature = signature;
        ReceivedAt = DateTime.UtcNow;
        Status = WebhookEventStatus.Pending;
    }

    public void MarkAsProcessing()
    {
        Status = WebhookEventStatus.Processing;
        UpdateTimestamp();
    }

    public void MarkAsProcessed(bool wasSuccessful, string? errorMessage = null, string? triggeredWorkflows = null)
    {
        ProcessedAt = DateTime.UtcNow;
        WasSuccessful = wasSuccessful;
        ErrorMessage = errorMessage;
        TriggeredWorkflows = triggeredWorkflows;
        Status = wasSuccessful ? WebhookEventStatus.Processed : WebhookEventStatus.Failed;
        
        if (ProcessedAt.HasValue)
        {
            ProcessingDuration = ProcessedAt.Value - ReceivedAt;
        }
        
        UpdateTimestamp();
    }

    public void MarkAsFailed(string errorMessage)
    {
        Status = WebhookEventStatus.Failed;
        WasSuccessful = false;
        ErrorMessage = errorMessage;
        ProcessedAt = DateTime.UtcNow;
        
        if (ProcessedAt.HasValue)
        {
            ProcessingDuration = ProcessedAt.Value - ReceivedAt;
        }
        
        UpdateTimestamp();
    }

    public void ScheduleRetry(DateTime nextRetryAt)
    {
        RetryCount++;
        NextRetryAt = nextRetryAt;
        Status = WebhookEventStatus.Retrying;
        UpdateTimestamp();
    }

    public void AddProcessingLog(string logEntry)
    {
        if (string.IsNullOrWhiteSpace(ProcessingLog))
        {
            ProcessingLog = logEntry;
        }
        else
        {
            ProcessingLog += "\n" + logEntry;
        }
        UpdateTimestamp();
    }

    public bool CanRetry(int maxRetries)
    {
        return RetryCount < maxRetries && 
               Status == WebhookEventStatus.Failed && 
               NextRetryAt.HasValue && 
               NextRetryAt.Value <= DateTime.UtcNow;
    }

    public bool IsStale(TimeSpan maxAge)
    {
        return DateTime.UtcNow - ReceivedAt > maxAge;
    }

    public void MarkAsIgnored(string reason)
    {
        Status = WebhookEventStatus.Ignored;
        ErrorMessage = reason;
        ProcessedAt = DateTime.UtcNow;
        UpdateTimestamp();
    }
}