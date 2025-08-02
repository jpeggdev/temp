using HeyDav.Domain.Common.Base;
using HeyDav.Domain.ToolIntegrations.Enums;
using HeyDav.Domain.ToolIntegrations.ValueObjects;

namespace HeyDav.Domain.ToolIntegrations.Entities;

public class ToolConnection : BaseEntity
{
    public string Name { get; private set; } = string.Empty;
    public string Description { get; private set; } = string.Empty;
    public ToolType ToolType { get; private set; }
    public ConnectionStatus Status { get; private set; }
    public AuthenticationMethod AuthMethod { get; private set; }
    public string BaseUrl { get; private set; } = string.Empty;
    public string ApiVersion { get; private set; } = string.Empty;
    public DateTime? LastHealthCheck { get; private set; }
    public DateTime? LastSuccessfulConnection { get; private set; }
    public string? LastErrorMessage { get; private set; }
    public int ConsecutiveFailures { get; private set; }
    public bool IsEnabled { get; private set; } = true;
    
    // Rate limiting settings
    public int RequestsPerMinute { get; private set; } = 60;
    public int RequestsPerHour { get; private set; } = 3600;
    public int RequestsPerDay { get; private set; } = 86400;
    
    // Configuration and credentials (encrypted)
    public ToolConfiguration Configuration { get; private set; } = new();
    public EncryptedCredentials Credentials { get; private set; } = new();
    
    // Navigation properties
    public virtual ICollection<ToolCapability> Capabilities { get; private set; } = new List<ToolCapability>();
    public virtual ICollection<WebhookEndpoint> WebhookEndpoints { get; private set; } = new List<WebhookEndpoint>();
    public virtual ICollection<ToolSyncConfiguration> SyncConfigurations { get; private set; } = new List<ToolSyncConfiguration>();

    private ToolConnection() { } // EF Core constructor

    public ToolConnection(
        string name,
        string description,
        ToolType toolType,
        AuthenticationMethod authMethod,
        string baseUrl,
        string apiVersion = "v1")
    {
        Name = name ?? throw new ArgumentNullException(nameof(name));
        Description = description ?? throw new ArgumentNullException(nameof(description));
        ToolType = toolType;
        AuthMethod = authMethod;
        BaseUrl = baseUrl ?? throw new ArgumentNullException(nameof(baseUrl));
        ApiVersion = apiVersion;
        Status = ConnectionStatus.Disconnected;
    }

    public void UpdateConfiguration(ToolConfiguration configuration)
    {
        Configuration = configuration ?? throw new ArgumentNullException(nameof(configuration));
        UpdateTimestamp();
    }

    public void UpdateCredentials(EncryptedCredentials credentials)
    {
        Credentials = credentials ?? throw new ArgumentNullException(nameof(credentials));
        UpdateTimestamp();
    }

    public void UpdateStatus(ConnectionStatus status, string? errorMessage = null)
    {
        Status = status;
        LastErrorMessage = errorMessage;
        
        if (status == ConnectionStatus.Connected)
        {
            LastSuccessfulConnection = DateTime.UtcNow;
            ConsecutiveFailures = 0;
        }
        else if (status == ConnectionStatus.Failed)
        {
            ConsecutiveFailures++;
        }
        
        UpdateTimestamp();
    }

    public void UpdateHealthCheck(bool isHealthy, string? errorMessage = null)
    {
        LastHealthCheck = DateTime.UtcNow;
        
        if (isHealthy)
        {
            if (Status != ConnectionStatus.Connected)
            {
                UpdateStatus(ConnectionStatus.Connected);
            }
        }
        else
        {
            UpdateStatus(ConnectionStatus.Failed, errorMessage);
        }
    }

    public void UpdateRateLimits(int requestsPerMinute, int requestsPerHour, int requestsPerDay)
    {
        RequestsPerMinute = Math.Max(1, requestsPerMinute);
        RequestsPerHour = Math.Max(1, requestsPerHour);
        RequestsPerDay = Math.Max(1, requestsPerDay);
        UpdateTimestamp();
    }

    public void Enable()
    {
        IsEnabled = true;
        UpdateTimestamp();
    }

    public void Disable()
    {
        IsEnabled = false;
        Status = ConnectionStatus.Disabled;
        UpdateTimestamp();
    }

    public void AddCapability(ToolCapability capability)
    {
        if (capability == null)
            throw new ArgumentNullException(nameof(capability));
            
        if (!Capabilities.Any(c => c.Name == capability.Name))
        {
            Capabilities.Add(capability);
            UpdateTimestamp();
        }
    }

    public void RemoveCapability(string capabilityName)
    {
        var capability = Capabilities.FirstOrDefault(c => c.Name == capabilityName);
        if (capability != null)
        {
            Capabilities.Remove(capability);
            UpdateTimestamp();
        }
    }

    public bool HasCapability(string capabilityName)
    {
        return Capabilities.Any(c => c.Name == capabilityName && c.IsEnabled);
    }

    public bool IsHealthy()
    {
        return Status == ConnectionStatus.Connected && 
               IsEnabled && 
               ConsecutiveFailures < 3 &&
               LastHealthCheck.HasValue &&
               LastHealthCheck.Value > DateTime.UtcNow.AddMinutes(-10);
    }
}