using HeyDav.Domain.Common.Base;
using HeyDav.Domain.ToolIntegrations.Enums;
using HeyDav.Domain.ToolIntegrations.ValueObjects;

namespace HeyDav.Domain.ToolIntegrations.Entities;

public class WebhookEndpoint : BaseEntity
{
    public Guid ToolConnectionId { get; private set; }
    public string Name { get; private set; } = string.Empty;
    public string Description { get; private set; } = string.Empty;
    public string EndpointUrl { get; private set; } = string.Empty;
    public string Secret { get; private set; } = string.Empty;
    public WebhookStatus Status { get; private set; }
    public string ContentType { get; private set; } = "application/json";
    public int TimeoutSeconds { get; private set; } = 30;
    public int MaxRetries { get; private set; } = 3;
    public bool IsEnabled { get; private set; } = true;
    
    // Event filtering
    public List<string> SupportedEvents { get; private set; } = new();
    public List<string> EnabledEvents { get; private set; } = new();
    
    // Security settings
    public WebhookSecuritySettings SecuritySettings { get; private set; } = new();
    
    // Statistics
    public int TotalEventsReceived { get; private set; }
    public int SuccessfulProcessing { get; private set; }
    public int FailedProcessing { get; private set; }
    public DateTime? LastEventReceived { get; private set; }
    public DateTime? LastSuccessfulEvent { get; private set; }
    public string? LastErrorMessage { get; private set; }
    
    // Navigation properties
    public virtual ToolConnection ToolConnection { get; private set; } = null!;
    public virtual ICollection<WebhookEvent> Events { get; private set; } = new List<WebhookEvent>();

    private WebhookEndpoint() { } // EF Core constructor

    public WebhookEndpoint(
        Guid toolConnectionId,
        string name,
        string description,
        string endpointUrl,
        string secret)
    {
        ToolConnectionId = toolConnectionId;
        Name = name ?? throw new ArgumentNullException(nameof(name));
        Description = description ?? throw new ArgumentNullException(nameof(description));
        EndpointUrl = endpointUrl ?? throw new ArgumentNullException(nameof(endpointUrl));
        Secret = secret ?? throw new ArgumentNullException(nameof(secret));
        Status = WebhookStatus.Active;
    }

    public void UpdateEndpointUrl(string endpointUrl)
    {
        EndpointUrl = endpointUrl ?? throw new ArgumentNullException(nameof(endpointUrl));
        UpdateTimestamp();
    }

    public void UpdateSecret(string secret)
    {
        Secret = secret ?? throw new ArgumentNullException(nameof(secret));
        UpdateTimestamp();
    }

    public void UpdateSecuritySettings(WebhookSecuritySettings securitySettings)
    {
        SecuritySettings = securitySettings ?? throw new ArgumentNullException(nameof(securitySettings));
        UpdateTimestamp();
    }

    public void UpdateSupportedEvents(List<string> supportedEvents)
    {
        SupportedEvents = supportedEvents ?? new List<string>();
        // Remove any enabled events that are no longer supported
        EnabledEvents = EnabledEvents.Where(e => SupportedEvents.Contains(e)).ToList();
        UpdateTimestamp();
    }

    public void EnableEvent(string eventType)
    {
        if (string.IsNullOrWhiteSpace(eventType))
            throw new ArgumentException("Event type cannot be null or empty", nameof(eventType));
            
        if (!SupportedEvents.Contains(eventType))
            throw new ArgumentException($"Event type '{eventType}' is not supported", nameof(eventType));
            
        if (!EnabledEvents.Contains(eventType))
        {
            EnabledEvents.Add(eventType);
            UpdateTimestamp();
        }
    }

    public void DisableEvent(string eventType)
    {
        if (EnabledEvents.Contains(eventType))
        {
            EnabledEvents.Remove(eventType);
            UpdateTimestamp();
        }
    }

    public void UpdateStatus(WebhookStatus status, string? errorMessage = null)
    {
        Status = status;
        if (status == WebhookStatus.Failed)
        {
            LastErrorMessage = errorMessage;
        }
        UpdateTimestamp();
    }

    public void Enable()
    {
        IsEnabled = true;
        if (Status == WebhookStatus.Disabled)
        {
            Status = WebhookStatus.Active;
        }
        UpdateTimestamp();
    }

    public void Disable()
    {
        IsEnabled = false;
        Status = WebhookStatus.Disabled;
        UpdateTimestamp();
    }

    public void RecordEventReceived(string eventType, bool wasSuccessful, string? errorMessage = null)
    {
        TotalEventsReceived++;
        LastEventReceived = DateTime.UtcNow;
        
        if (wasSuccessful)
        {
            SuccessfulProcessing++;
            LastSuccessfulEvent = DateTime.UtcNow;
            if (Status == WebhookStatus.Failed)
            {
                Status = WebhookStatus.Active;
            }
        }
        else
        {
            FailedProcessing++;
            LastErrorMessage = errorMessage;
            
            // Mark as failed if too many consecutive failures
            if (GetRecentFailureRate() > 0.8)
            {
                Status = WebhookStatus.Failed;
            }
        }
        
        UpdateTimestamp();
    }

    public bool IsEventEnabled(string eventType)
    {
        return IsEnabled && EnabledEvents.Contains(eventType);
    }

    public double GetSuccessRate()
    {
        if (TotalEventsReceived == 0) return 1.0;
        return (double)SuccessfulProcessing / TotalEventsReceived;
    }

    public double GetRecentFailureRate()
    {
        // Calculate failure rate for recent events (last 10)
        var recentEvents = Events
            .OrderByDescending(e => e.CreatedAt)
            .Take(10)
            .ToList();
            
        if (recentEvents.Count == 0) return 0.0;
        
        var failedRecent = recentEvents.Count(e => !e.WasSuccessful);
        return (double)failedRecent / recentEvents.Count;
    }

    public bool IsHealthy()
    {
        return IsEnabled && 
               Status == WebhookStatus.Active && 
               GetSuccessRate() > 0.8 &&
               GetRecentFailureRate() < 0.5;
    }
}