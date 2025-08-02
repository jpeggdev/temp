using Microsoft.Extensions.Logging;
using HeyDav.Domain.ToolIntegrations.Entities;
using HeyDav.Domain.ToolIntegrations.Enums;
using HeyDav.Domain.ToolIntegrations.Interfaces;
using HeyDav.Domain.ToolIntegrations.ValueObjects;
using HeyDav.Application.ToolIntegrations.Interfaces;
using HeyDav.Application.ToolIntegrations.Models;

namespace HeyDav.Application.ToolIntegrations.Services;

public class ToolIntegrationManager : IToolIntegrationManager
{
    private readonly IToolConnectionRepository _connectionRepository;
    private readonly IToolCapabilityRepository _capabilityRepository;
    private readonly IToolAuthenticationService _authService;
    private readonly IToolHealthMonitor _healthMonitor;
    private readonly IRateLimitManager _rateLimitManager;
    private readonly IToolCapabilityDiscovery _capabilityDiscovery;
    private readonly ILogger<ToolIntegrationManager> _logger;

    public ToolIntegrationManager(
        IToolConnectionRepository connectionRepository,
        IToolCapabilityRepository capabilityRepository,
        IToolAuthenticationService authService,
        IToolHealthMonitor healthMonitor,
        IRateLimitManager rateLimitManager,
        IToolCapabilityDiscovery capabilityDiscovery,
        ILogger<ToolIntegrationManager> logger)
    {
        _connectionRepository = connectionRepository;
        _capabilityRepository = capabilityRepository;
        _authService = authService;
        _healthMonitor = healthMonitor;
        _rateLimitManager = rateLimitManager;
        _capabilityDiscovery = capabilityDiscovery;
        _logger = logger;
    }

    public async Task<ToolConnection> CreateConnectionAsync(CreateToolConnectionRequest request, CancellationToken cancellationToken = default)
    {
        _logger.LogInformation("Creating tool connection: {Name}", request.Name);

        // Check if connection already exists
        var existingConnection = await _connectionRepository.GetByNameAsync(request.Name, cancellationToken);
        if (existingConnection != null)
        {
            throw new ToolIntegrationException($"Connection with name '{request.Name}' already exists");
        }

        var connection = new ToolConnection(
            request.Name,
            request.Description,
            request.ToolType,
            request.AuthMethod,
            request.BaseUrl,
            request.ApiVersion);

        if (request.Configuration != null)
        {
            connection.UpdateConfiguration(request.Configuration);
        }

        connection.UpdateRateLimits(request.RequestsPerMinute, request.RequestsPerHour, request.RequestsPerDay);

        if (request.Credentials != null && request.Credentials.Count > 0)
        {
            var encryptedCredentials = await EncryptCredentialsAsync(request.Credentials, request.ToolType);
            connection.UpdateCredentials(encryptedCredentials);
        }

        await _connectionRepository.AddAsync(connection, cancellationToken);

        _logger.LogInformation("Tool connection created successfully: {ConnectionId}", connection.Id);

        // Attempt initial authentication if credentials provided
        if (request.Credentials != null)
        {
            try
            {
                await AuthenticateConnectionAsync(connection.Id, cancellationToken);
            }
            catch (Exception ex)
            {
                _logger.LogWarning(ex, "Initial authentication failed for connection {ConnectionId}", connection.Id);
            }
        }

        return connection;
    }

    public async Task<ToolConnection?> GetConnectionAsync(Guid connectionId, CancellationToken cancellationToken = default)
    {
        return await _connectionRepository.GetByIdAsync(connectionId, cancellationToken);
    }

    public async Task<IEnumerable<ToolConnection>> GetConnectionsAsync(CancellationToken cancellationToken = default)
    {
        return await _connectionRepository.ListAsync(cancellationToken);
    }

    public async Task<IEnumerable<ToolConnection>> GetConnectionsByTypeAsync(ToolType toolType, CancellationToken cancellationToken = default)
    {
        return await _connectionRepository.GetByTypeAsync(toolType, cancellationToken);
    }

    public async Task<ToolConnection> UpdateConnectionAsync(Guid connectionId, UpdateToolConnectionRequest request, CancellationToken cancellationToken = default)
    {
        var connection = await _connectionRepository.GetByIdAsync(connectionId, cancellationToken);
        if (connection == null)
        {
            throw new ToolIntegrationException($"Connection with ID '{connectionId}' not found");
        }

        _logger.LogInformation("Updating tool connection: {ConnectionId}", connectionId);

        // Update basic properties using reflection or manual updates
        // Note: In a real implementation, you'd want proper update methods on the entity
        if (request.Configuration != null)
        {
            connection.UpdateConfiguration(request.Configuration);
        }

        if (request.RequestsPerMinute.HasValue || request.RequestsPerHour.HasValue || request.RequestsPerDay.HasValue)
        {
            connection.UpdateRateLimits(
                request.RequestsPerMinute ?? connection.RequestsPerMinute,
                request.RequestsPerHour ?? connection.RequestsPerHour,
                request.RequestsPerDay ?? connection.RequestsPerDay);
        }

        await _connectionRepository.UpdateAsync(connection, cancellationToken);

        _logger.LogInformation("Tool connection updated successfully: {ConnectionId}", connectionId);

        return connection;
    }

    public async Task DeleteConnectionAsync(Guid connectionId, CancellationToken cancellationToken = default)
    {
        var connection = await _connectionRepository.GetByIdAsync(connectionId, cancellationToken);
        if (connection == null)
        {
            throw new ToolIntegrationException($"Connection with ID '{connectionId}' not found");
        }

        _logger.LogInformation("Deleting tool connection: {ConnectionId}", connectionId);

        await _connectionRepository.DeleteAsync(connectionId, cancellationToken);

        _logger.LogInformation("Tool connection deleted successfully: {ConnectionId}", connectionId);
    }

    public async Task<ToolConnection> EnableConnectionAsync(Guid connectionId, CancellationToken cancellationToken = default)
    {
        var connection = await _connectionRepository.GetByIdAsync(connectionId, cancellationToken);
        if (connection == null)
        {
            throw new ToolIntegrationException($"Connection with ID '{connectionId}' not found");
        }

        connection.Enable();
        await _connectionRepository.UpdateAsync(connection, cancellationToken);

        _logger.LogInformation("Tool connection enabled: {ConnectionId}", connectionId);

        return connection;
    }

    public async Task<ToolConnection> DisableConnectionAsync(Guid connectionId, CancellationToken cancellationToken = default)
    {
        var connection = await _connectionRepository.GetByIdAsync(connectionId, cancellationToken);
        if (connection == null)
        {
            throw new ToolIntegrationException($"Connection with ID '{connectionId}' not found");
        }

        connection.Disable();
        await _connectionRepository.UpdateAsync(connection, cancellationToken);

        _logger.LogInformation("Tool connection disabled: {ConnectionId}", connectionId);

        return connection;
    }

    public async Task<bool> AuthenticateConnectionAsync(Guid connectionId, CancellationToken cancellationToken = default)
    {
        var connection = await _connectionRepository.GetByIdAsync(connectionId, cancellationToken);
        if (connection == null)
        {
            throw new ToolIntegrationException($"Connection with ID '{connectionId}' not found");
        }

        _logger.LogInformation("Authenticating connection: {ConnectionId}", connectionId);

        var result = await _authService.AuthenticateAsync(connection, cancellationToken);
        
        if (result.IsSuccessful)
        {
            // Update credentials if new tokens received
            if (!string.IsNullOrEmpty(result.AccessToken))
            {
                var updatedCredentials = connection.Credentials
                    .WithAccessToken(await _authService.EncryptCredentialsAsync(result.AccessToken, cancellationToken), result.ExpiresAt);
                
                if (!string.IsNullOrEmpty(result.RefreshToken))
                {
                    updatedCredentials = updatedCredentials.WithRefreshToken(
                        await _authService.EncryptCredentialsAsync(result.RefreshToken, cancellationToken));
                }

                if (result.Scopes != null)
                {
                    updatedCredentials = updatedCredentials.WithScopes(result.Scopes);
                }

                connection.UpdateCredentials(updatedCredentials);
            }

            connection.UpdateStatus(ConnectionStatus.Connected);
            await _connectionRepository.UpdateAsync(connection, cancellationToken);

            // Discover capabilities after successful authentication
            try
            {
                await _capabilityDiscovery.UpdateCapabilitiesAsync(connection, cancellationToken);
            }
            catch (Exception ex)
            {
                _logger.LogWarning(ex, "Failed to discover capabilities for connection {ConnectionId}", connectionId);
            }

            _logger.LogInformation("Connection authenticated successfully: {ConnectionId}", connectionId);
        }
        else
        {
            connection.UpdateStatus(ConnectionStatus.Failed, result.ErrorMessage);
            await _connectionRepository.UpdateAsync(connection, cancellationToken);
            
            _logger.LogWarning("Authentication failed for connection {ConnectionId}: {Error}", connectionId, result.ErrorMessage);
        }

        return result.IsSuccessful;
    }

    public async Task<bool> RefreshAuthenticationAsync(Guid connectionId, CancellationToken cancellationToken = default)
    {
        var connection = await _connectionRepository.GetByIdAsync(connectionId, cancellationToken);
        if (connection == null)
        {
            throw new ToolIntegrationException($"Connection with ID '{connectionId}' not found");
        }

        _logger.LogInformation("Refreshing authentication for connection: {ConnectionId}", connectionId);

        var result = await _authService.RefreshTokenAsync(connection, cancellationToken);
        
        if (result.IsSuccessful)
        {
            // Update credentials with new tokens
            var updatedCredentials = connection.Credentials;
            
            if (!string.IsNullOrEmpty(result.AccessToken))
            {
                updatedCredentials = updatedCredentials.WithAccessToken(
                    await _authService.EncryptCredentialsAsync(result.AccessToken, cancellationToken), 
                    result.ExpiresAt);
            }

            if (!string.IsNullOrEmpty(result.RefreshToken))
            {
                updatedCredentials = updatedCredentials.WithRefreshToken(
                    await _authService.EncryptCredentialsAsync(result.RefreshToken, cancellationToken));
            }

            connection.UpdateCredentials(updatedCredentials);
            connection.UpdateStatus(ConnectionStatus.Connected);
            await _connectionRepository.UpdateAsync(connection, cancellationToken);

            _logger.LogInformation("Authentication refreshed successfully: {ConnectionId}", connectionId);
        }
        else
        {
            connection.UpdateStatus(ConnectionStatus.Expired, result.ErrorMessage);
            await _connectionRepository.UpdateAsync(connection, cancellationToken);
            
            _logger.LogWarning("Authentication refresh failed for connection {ConnectionId}: {Error}", connectionId, result.ErrorMessage);
        }

        return result.IsSuccessful;
    }

    public async Task<bool> ValidateCredentialsAsync(Guid connectionId, CancellationToken cancellationToken = default)
    {
        var connection = await _connectionRepository.GetByIdAsync(connectionId, cancellationToken);
        if (connection == null)
        {
            throw new ToolIntegrationException($"Connection with ID '{connectionId}' not found");
        }

        return await _authService.ValidateTokenAsync(connection, cancellationToken);
    }

    public async Task UpdateCredentialsAsync(Guid connectionId, UpdateCredentialsRequest request, CancellationToken cancellationToken = default)
    {
        var connection = await _connectionRepository.GetByIdAsync(connectionId, cancellationToken);
        if (connection == null)
        {
            throw new ToolIntegrationException($"Connection with ID '{connectionId}' not found");
        }

        _logger.LogInformation("Updating credentials for connection: {ConnectionId}", connectionId);

        var encryptedCredentials = await EncryptCredentialsAsync(request.Credentials, connection.ToolType);
        
        if (request.Scopes != null)
        {
            encryptedCredentials = encryptedCredentials.WithScopes(request.Scopes);
        }

        connection.UpdateCredentials(encryptedCredentials);
        await _connectionRepository.UpdateAsync(connection, cancellationToken);

        // Re-authenticate with new credentials
        await AuthenticateConnectionAsync(connectionId, cancellationToken);

        _logger.LogInformation("Credentials updated successfully: {ConnectionId}", connectionId);
    }

    public async Task<HealthCheckResult> CheckConnectionHealthAsync(Guid connectionId, CancellationToken cancellationToken = default)
    {
        var connection = await _connectionRepository.GetByIdAsync(connectionId, cancellationToken);
        if (connection == null)
        {
            throw new ToolIntegrationException($"Connection with ID '{connectionId}' not found");
        }

        return await _healthMonitor.CheckHealthAsync(connection, cancellationToken);
    }

    public async Task<IEnumerable<HealthCheckResult>> CheckAllConnectionsHealthAsync(CancellationToken cancellationToken = default)
    {
        return await _healthMonitor.CheckAllHealthAsync(cancellationToken);
    }

    public async Task<ConnectionMetrics> GetConnectionMetricsAsync(Guid connectionId, CancellationToken cancellationToken = default)
    {
        var connection = await _connectionRepository.GetByIdAsync(connectionId, cancellationToken);
        if (connection == null)
        {
            throw new ToolIntegrationException($"Connection with ID '{connectionId}' not found");
        }

        var capabilities = await _capabilityRepository.GetByToolConnectionIdAsync(connectionId, cancellationToken);
        var rateLimitStatus = await GetRateLimitStatusAsync(connectionId, null, cancellationToken);

        var totalUsage = capabilities.Sum(c => c.TotalUsageCount);
        var successfulUsage = capabilities.Sum(c => c.SuccessfulOperations);
        var failedUsage = capabilities.Sum(c => c.FailedOperations);

        return new ConnectionMetrics(
            ConnectionId: connectionId,
            ConnectionName: connection.Name,
            TotalRequests: totalUsage,
            SuccessfulRequests: successfulUsage,
            FailedRequests: failedUsage,
            SuccessRate: totalUsage > 0 ? (double)successfulUsage / totalUsage : 1.0,
            AverageResponseTime: TimeSpan.Zero, // Would need to track this separately
            LastRequest: capabilities.Max(c => c.LastUsed),
            LastSuccessfulRequest: connection.LastSuccessfulConnection,
            RateLimitStatus: rateLimitStatus,
            CustomMetrics: new Dictionary<string, object>
            {
                ["ConsecutiveFailures"] = connection.ConsecutiveFailures,
                ["IsHealthy"] = connection.IsHealthy(),
                ["CapabilityCount"] = capabilities.Count()
            });
    }

    public async Task<bool> CanMakeRequestAsync(Guid connectionId, string? capabilityName = null, CancellationToken cancellationToken = default)
    {
        var connection = await _connectionRepository.GetByIdAsync(connectionId, cancellationToken);
        if (connection == null || !connection.IsEnabled)
        {
            return false;
        }

        var rateLimitKey = $"connection:{connectionId}";
        if (!string.IsNullOrEmpty(capabilityName))
        {
            rateLimitKey += $":capability:{capabilityName}";
        }

        var rateLimitRule = new RateLimitRule(
            connection.RequestsPerMinute,
            connection.RequestsPerHour,
            connection.RequestsPerDay,
            RateLimitScope.PerConnection);

        return await _rateLimitManager.CanMakeRequestAsync(rateLimitKey, rateLimitRule, cancellationToken);
    }

    public async Task RecordRequestAsync(Guid connectionId, string? capabilityName = null, bool wasSuccessful = true, CancellationToken cancellationToken = default)
    {
        var connection = await _connectionRepository.GetByIdAsync(connectionId, cancellationToken);
        if (connection == null)
        {
            return;
        }

        var rateLimitKey = $"connection:{connectionId}";
        if (!string.IsNullOrEmpty(capabilityName))
        {
            rateLimitKey += $":capability:{capabilityName}";
        }

        var rateLimitRule = new RateLimitRule(
            connection.RequestsPerMinute,
            connection.RequestsPerHour,
            connection.RequestsPerDay,
            RateLimitScope.PerConnection);

        await _rateLimitManager.RecordRequestAsync(rateLimitKey, rateLimitRule, cancellationToken);

        // Update capability usage if specified
        if (!string.IsNullOrEmpty(capabilityName))
        {
            var capability = await _capabilityRepository.GetByNameAndConnectionAsync(capabilityName, connectionId, cancellationToken);
            if (capability != null)
            {
                capability.RecordUsage(wasSuccessful);
                await _capabilityRepository.UpdateAsync(capability, cancellationToken);
            }
        }
    }

    public async Task<RateLimitStatus> GetRateLimitStatusAsync(Guid connectionId, string? capabilityName = null, CancellationToken cancellationToken = default)
    {
        var connection = await _connectionRepository.GetByIdAsync(connectionId, cancellationToken);
        if (connection == null)
        {
            throw new ToolIntegrationException($"Connection with ID '{connectionId}' not found");
        }

        var rateLimitKey = $"connection:{connectionId}";
        if (!string.IsNullOrEmpty(capabilityName))
        {
            rateLimitKey += $":capability:{capabilityName}";
        }

        var rateLimitRule = new RateLimitRule(
            connection.RequestsPerMinute,
            connection.RequestsPerHour,
            connection.RequestsPerDay,
            RateLimitScope.PerConnection);

        return await _rateLimitManager.GetStatusAsync(rateLimitKey, rateLimitRule, cancellationToken);
    }

    public async Task<IEnumerable<ToolCapability>> GetCapabilitiesAsync(Guid connectionId, CancellationToken cancellationToken = default)
    {
        return await _capabilityRepository.GetByToolConnectionIdAsync(connectionId, cancellationToken);
    }

    public async Task<ToolCapability?> GetCapabilityAsync(Guid connectionId, string capabilityName, CancellationToken cancellationToken = default)
    {
        return await _capabilityRepository.GetByNameAndConnectionAsync(capabilityName, connectionId, cancellationToken);
    }

    public async Task<bool> HasCapabilityAsync(Guid connectionId, string capabilityName, CancellationToken cancellationToken = default)
    {
        var connection = await _connectionRepository.GetByIdAsync(connectionId, cancellationToken);
        return connection?.HasCapability(capabilityName) ?? false;
    }

    public async Task<CapabilityResult> ExecuteCapabilityAsync(Guid connectionId, string capabilityName, object? parameters = null, CancellationToken cancellationToken = default)
    {
        var startTime = DateTime.UtcNow;
        
        try
        {
            // Check if we can make the request (rate limiting)
            if (!await CanMakeRequestAsync(connectionId, capabilityName, cancellationToken))
            {
                var rateLimitStatus = await GetRateLimitStatusAsync(connectionId, capabilityName, cancellationToken);
                throw new RateLimitExceededException(
                    "Rate limit exceeded for this capability",
                    rateLimitStatus.RetryAfter ?? TimeSpan.FromMinutes(1));
            }

            // Check if capability exists and is enabled
            var capability = await GetCapabilityAsync(connectionId, capabilityName, cancellationToken);
            if (capability == null)
            {
                throw new CapabilityNotSupportedException(capabilityName, $"Capability '{capabilityName}' not found");
            }

            if (!capability.IsEnabled)
            {
                throw new CapabilityNotSupportedException(capabilityName, $"Capability '{capabilityName}' is disabled");
            }

            // Record the request attempt
            await RecordRequestAsync(connectionId, capabilityName, true, cancellationToken);

            // TODO: Implement actual capability execution based on capability type and configuration
            // This would involve calling the appropriate tool's API with the right parameters

            var executionTime = DateTime.UtcNow - startTime;
            
            return new CapabilityResult(
                IsSuccessful: true,
                Data: "Capability executed successfully", // Placeholder
                ExecutionTime: executionTime);
        }
        catch (Exception ex)
        {
            await RecordRequestAsync(connectionId, capabilityName, false, cancellationToken);
            
            var executionTime = DateTime.UtcNow - startTime;
            
            return new CapabilityResult(
                IsSuccessful: false,
                ErrorMessage: ex.Message,
                ExecutionTime: executionTime);
        }
    }

    public async Task<IEnumerable<ToolDiscoveryResult>> DiscoverAvailableToolsAsync(CancellationToken cancellationToken = default)
    {
        // TODO: Implement tool discovery logic
        // This could involve checking well-known APIs, configuration files, or external registries
        
        return new List<ToolDiscoveryResult>
        {
            new("GitHub", ToolType.VersionControl, "GitHub API integration", "https://api.github.com", AuthenticationMethod.OAuth2, new List<string> { "repositories", "issues", "pull_requests" }, new Dictionary<string, object>()),
            new("Slack", ToolType.Communication, "Slack API integration", "https://slack.com/api", AuthenticationMethod.OAuth2, new List<string> { "messages", "channels", "users" }, new Dictionary<string, object>()),
            new("Trello", ToolType.ProjectManagement, "Trello API integration", "https://api.trello.com", AuthenticationMethod.OAuth2, new List<string> { "boards", "cards", "lists" }, new Dictionary<string, object>()),
            new("Notion", ToolType.Documentation, "Notion API integration", "https://api.notion.com", AuthenticationMethod.OAuth2, new List<string> { "pages", "databases", "blocks" }, new Dictionary<string, object>())
        };
    }

    public async Task<ToolCapabilityDiscovery> DiscoverToolCapabilitiesAsync(Guid connectionId, CancellationToken cancellationToken = default)
    {
        var connection = await _connectionRepository.GetByIdAsync(connectionId, cancellationToken);
        if (connection == null)
        {
            throw new ToolIntegrationException($"Connection with ID '{connectionId}' not found");
        }

        return await _capabilityDiscovery.DiscoverCapabilitiesAsync(connection, cancellationToken);
    }

    private async Task<EncryptedCredentials> EncryptCredentialsAsync(Dictionary<string, string> credentials, ToolType toolType)
    {
        var encryptedCredentials = new EncryptedCredentials();

        foreach (var credential in credentials)
        {
            var encryptedValue = await _authService.EncryptCredentialsAsync(credential.Value);
            
            encryptedCredentials = credential.Key.ToLowerInvariant() switch
            {
                "apikey" or "api_key" => encryptedCredentials.WithApiKey(encryptedValue),
                "clientid" or "client_id" => encryptedCredentials.WithOAuthCredentials(encryptedValue, encryptedCredentials.EncryptedClientSecret ?? string.Empty),
                "clientsecret" or "client_secret" => encryptedCredentials.WithOAuthCredentials(encryptedCredentials.EncryptedClientId ?? string.Empty, encryptedValue),
                "accesstoken" or "access_token" => encryptedCredentials.WithAccessToken(encryptedValue),
                "refreshtoken" or "refresh_token" => encryptedCredentials.WithRefreshToken(encryptedValue),
                "username" => encryptedCredentials.WithBasicAuth(encryptedValue, encryptedCredentials.EncryptedPassword ?? string.Empty),
                "password" => encryptedCredentials.WithBasicAuth(encryptedCredentials.EncryptedUsername ?? string.Empty, encryptedValue),
                _ => encryptedCredentials.WithCustomField(credential.Key, encryptedValue)
            };
        }

        return encryptedCredentials;
    }
}