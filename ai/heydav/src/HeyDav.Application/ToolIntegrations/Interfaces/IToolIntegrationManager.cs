using HeyDav.Domain.ToolIntegrations.Entities;
using HeyDav.Domain.ToolIntegrations.Enums;
using HeyDav.Application.ToolIntegrations.Models;

namespace HeyDav.Application.ToolIntegrations.Interfaces;

public interface IToolIntegrationManager
{
    // Connection Management
    Task<ToolConnection> CreateConnectionAsync(CreateToolConnectionRequest request, CancellationToken cancellationToken = default);
    Task<ToolConnection?> GetConnectionAsync(Guid connectionId, CancellationToken cancellationToken = default);
    Task<IEnumerable<ToolConnection>> GetConnectionsAsync(CancellationToken cancellationToken = default);
    Task<IEnumerable<ToolConnection>> GetConnectionsByTypeAsync(ToolType toolType, CancellationToken cancellationToken = default);
    Task<ToolConnection> UpdateConnectionAsync(Guid connectionId, UpdateToolConnectionRequest request, CancellationToken cancellationToken = default);
    Task DeleteConnectionAsync(Guid connectionId, CancellationToken cancellationToken = default);
    Task<ToolConnection> EnableConnectionAsync(Guid connectionId, CancellationToken cancellationToken = default);
    Task<ToolConnection> DisableConnectionAsync(Guid connectionId, CancellationToken cancellationToken = default);

    // Authentication Management
    Task<bool> AuthenticateConnectionAsync(Guid connectionId, CancellationToken cancellationToken = default);
    Task<bool> RefreshAuthenticationAsync(Guid connectionId, CancellationToken cancellationToken = default);
    Task<bool> ValidateCredentialsAsync(Guid connectionId, CancellationToken cancellationToken = default);
    Task UpdateCredentialsAsync(Guid connectionId, UpdateCredentialsRequest request, CancellationToken cancellationToken = default);

    // Health Monitoring
    Task<HealthCheckResult> CheckConnectionHealthAsync(Guid connectionId, CancellationToken cancellationToken = default);
    Task<IEnumerable<HealthCheckResult>> CheckAllConnectionsHealthAsync(CancellationToken cancellationToken = default);
    Task<ConnectionMetrics> GetConnectionMetricsAsync(Guid connectionId, CancellationToken cancellationToken = default);

    // Rate Limiting
    Task<bool> CanMakeRequestAsync(Guid connectionId, string? capabilityName = null, CancellationToken cancellationToken = default);
    Task RecordRequestAsync(Guid connectionId, string? capabilityName = null, bool wasSuccessful = true, CancellationToken cancellationToken = default);
    Task<RateLimitStatus> GetRateLimitStatusAsync(Guid connectionId, string? capabilityName = null, CancellationToken cancellationToken = default);

    // Capability Management
    Task<IEnumerable<ToolCapability>> GetCapabilitiesAsync(Guid connectionId, CancellationToken cancellationToken = default);
    Task<ToolCapability?> GetCapabilityAsync(Guid connectionId, string capabilityName, CancellationToken cancellationToken = default);
    Task<bool> HasCapabilityAsync(Guid connectionId, string capabilityName, CancellationToken cancellationToken = default);
    Task<CapabilityResult> ExecuteCapabilityAsync(Guid connectionId, string capabilityName, object? parameters = null, CancellationToken cancellationToken = default);

    // Tool Discovery
    Task<IEnumerable<ToolDiscoveryResult>> DiscoverAvailableToolsAsync(CancellationToken cancellationToken = default);
    Task<ToolCapabilityDiscovery> DiscoverToolCapabilitiesAsync(Guid connectionId, CancellationToken cancellationToken = default);
}

public interface IRateLimitManager
{
    Task<bool> CanMakeRequestAsync(string key, RateLimitRule rule, CancellationToken cancellationToken = default);
    Task RecordRequestAsync(string key, RateLimitRule rule, CancellationToken cancellationToken = default);
    Task<RateLimitStatus> GetStatusAsync(string key, RateLimitRule rule, CancellationToken cancellationToken = default);
    Task ResetLimitsAsync(string key, CancellationToken cancellationToken = default);
}

public interface IToolAuthenticationService
{
    Task<AuthenticationResult> AuthenticateAsync(ToolConnection connection, CancellationToken cancellationToken = default);
    Task<AuthenticationResult> RefreshTokenAsync(ToolConnection connection, CancellationToken cancellationToken = default);
    Task<bool> ValidateTokenAsync(ToolConnection connection, CancellationToken cancellationToken = default);
    Task<string> EncryptCredentialsAsync(string value, CancellationToken cancellationToken = default);
    Task<string> DecryptCredentialsAsync(string encryptedValue, CancellationToken cancellationToken = default);
}

public interface IToolHealthMonitor
{
    Task<HealthCheckResult> CheckHealthAsync(ToolConnection connection, CancellationToken cancellationToken = default);
    Task<IEnumerable<HealthCheckResult>> CheckAllHealthAsync(CancellationToken cancellationToken = default);
    Task StartMonitoringAsync(CancellationToken cancellationToken = default);
    Task StopMonitoringAsync(CancellationToken cancellationToken = default);
}

public interface IToolCapabilityDiscovery
{
    Task<ToolCapabilityDiscovery> DiscoverCapabilitiesAsync(ToolConnection connection, CancellationToken cancellationToken = default);
    Task<bool> TestCapabilityAsync(ToolConnection connection, string capabilityName, CancellationToken cancellationToken = default);
    Task UpdateCapabilitiesAsync(ToolConnection connection, CancellationToken cancellationToken = default);
}