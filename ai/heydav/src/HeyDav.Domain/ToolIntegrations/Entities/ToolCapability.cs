using HeyDav.Domain.Common.Base;
using HeyDav.Domain.ToolIntegrations.Enums;
using HeyDav.Domain.ToolIntegrations.ValueObjects;

namespace HeyDav.Domain.ToolIntegrations.Entities;

public class ToolCapability : BaseEntity
{
    public Guid ToolConnectionId { get; private set; }
    public string Name { get; private set; } = string.Empty;
    public string Description { get; private set; } = string.Empty;
    public CapabilityType Type { get; private set; }
    public bool IsEnabled { get; private set; } = true;
    public bool RequiresAuthentication { get; private set; }
    public string? RequiredScopes { get; private set; }
    public CapabilityConfiguration Configuration { get; private set; } = new();
    
    // Rate limiting specific to this capability
    public int? MaxRequestsPerMinute { get; private set; }
    public int? MaxRequestsPerHour { get; private set; }
    
    // Usage tracking
    public int TotalUsageCount { get; private set; }
    public DateTime? LastUsed { get; private set; }
    public int SuccessfulOperations { get; private set; }
    public int FailedOperations { get; private set; }
    
    // Navigation properties
    public virtual ToolConnection ToolConnection { get; private set; } = null!;

    private ToolCapability() { } // EF Core constructor

    public ToolCapability(
        Guid toolConnectionId,
        string name,
        string description,
        CapabilityType type,
        bool requiresAuthentication = false,
        string? requiredScopes = null)
    {
        ToolConnectionId = toolConnectionId;
        Name = name ?? throw new ArgumentNullException(nameof(name));
        Description = description ?? throw new ArgumentNullException(nameof(description));
        Type = type;
        RequiresAuthentication = requiresAuthentication;
        RequiredScopes = requiredScopes;
    }

    public void UpdateConfiguration(CapabilityConfiguration configuration)
    {
        Configuration = configuration ?? throw new ArgumentNullException(nameof(configuration));
        UpdateTimestamp();
    }

    public void UpdateRateLimits(int? maxRequestsPerMinute, int? maxRequestsPerHour)
    {
        MaxRequestsPerMinute = maxRequestsPerMinute;
        MaxRequestsPerHour = maxRequestsPerHour;
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
        UpdateTimestamp();
    }

    public void RecordUsage(bool wasSuccessful)
    {
        TotalUsageCount++;
        LastUsed = DateTime.UtcNow;
        
        if (wasSuccessful)
        {
            SuccessfulOperations++;
        }
        else
        {
            FailedOperations++;
        }
        
        UpdateTimestamp();
    }

    public double GetSuccessRate()
    {
        if (TotalUsageCount == 0) return 0.0;
        return (double)SuccessfulOperations / TotalUsageCount;
    }

    public bool IsHealthy()
    {
        return IsEnabled && 
               (TotalUsageCount == 0 || GetSuccessRate() > 0.8) &&
               FailedOperations < 10;
    }
}