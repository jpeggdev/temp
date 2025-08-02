using Microsoft.Extensions.Logging;
using System.Text.Json;
using HeyDav.Domain.ToolIntegrations.Entities;
using HeyDav.Domain.ToolIntegrations.Enums;
using HeyDav.Application.ToolIntegrations.Models;

namespace HeyDav.Infrastructure.ExternalTools.Common;

public abstract class BaseToolIntegration
{
    protected readonly ILogger Logger;

    protected BaseToolIntegration(ILogger logger)
    {
        Logger = logger;
    }

    public abstract string ToolName { get; }
    public abstract ToolType ToolType { get; }
    public abstract string BaseUrl { get; }

    public abstract Task<AuthenticationResult> AuthenticateAsync(ToolConnection connection, CancellationToken cancellationToken = default);
    public abstract Task<ToolCapabilityDiscovery> DiscoverCapabilitiesAsync(ToolConnection connection, CancellationToken cancellationToken = default);
    public abstract Task<CapabilityResult> ExecuteCapabilityAsync(ToolConnection connection, string capabilityName, object? parameters = null, CancellationToken cancellationToken = default);
    public abstract Task<bool> TestConnectionAsync(ToolConnection connection, CancellationToken cancellationToken = default);

    protected virtual async Task<string?> GetDecryptedCredentialAsync(string? encryptedCredential)
    {
        // TODO: Implement actual decryption logic
        // This is a placeholder - in a real implementation, you'd use a proper encryption service
        await Task.CompletedTask;
        return encryptedCredential;
    }

    protected static Dictionary<string, object> ExtractParameters(object? parameters)
    {
        if (parameters == null)
            return new Dictionary<string, object>();

        if (parameters is Dictionary<string, object> dict)
        {
            return dict;
        }

        if (parameters is string json)
        {
            try
            {
                var jsonDoc = JsonDocument.Parse(json);
                var result = new Dictionary<string, object>();
                
                foreach (var property in jsonDoc.RootElement.EnumerateObject())
                {
                    result[property.Name] = property.Value.GetRawText();
                }
                
                return result;
            }
            catch (JsonException)
            {
                return new Dictionary<string, object>();
            }
        }

        // Use reflection to convert object properties to dictionary
        var result2 = new Dictionary<string, object>();
        var properties = parameters.GetType().GetProperties();
        
        foreach (var property in properties)
        {
            var value = property.GetValue(parameters);
            if (value != null)
            {
                result2[property.Name] = value;
            }
        }

        return result2;
    }

    protected virtual async Task<HealthCheckResult> PerformHealthCheckAsync(ToolConnection connection, CancellationToken cancellationToken = default)
    {
        var startTime = DateTime.UtcNow;
        
        try
        {
            var isHealthy = await TestConnectionAsync(connection, cancellationToken);
            var responseTime = DateTime.UtcNow - startTime;

            return new HealthCheckResult(
                connection.Id,
                connection.Name,
                isHealthy,
                isHealthy ? ConnectionStatus.Connected : ConnectionStatus.Failed,
                isHealthy ? null : "Connection test failed",
                responseTime);
        }
        catch (Exception ex)
        {
            var responseTime = DateTime.UtcNow - startTime;
            Logger.LogError(ex, "Health check failed for connection {ConnectionId}", connection.Id);
            
            return new HealthCheckResult(
                connection.Id,
                connection.Name,
                false,
                ConnectionStatus.Failed,
                ex.Message,
                responseTime);
        }
    }

    protected virtual RateLimitRule GetDefaultRateLimitRule()
    {
        return new RateLimitRule(
            RequestsPerMinute: 60,
            RequestsPerHour: 3600,
            RequestsPerDay: 86400,
            Scope: RateLimitScope.PerConnection);
    }

    protected virtual async Task<bool> ValidateParametersAsync(string capabilityName, object? parameters, CancellationToken cancellationToken = default)
    {
        // TODO: Implement parameter validation logic based on capability requirements
        await Task.CompletedTask;
        return true;
    }
}

public interface IToolIntegration
{
    string ToolName { get; }
    ToolType ToolType { get; }
    string BaseUrl { get; }
    
    Task<AuthenticationResult> AuthenticateAsync(ToolConnection connection, CancellationToken cancellationToken = default);
    Task<ToolCapabilityDiscovery> DiscoverCapabilitiesAsync(ToolConnection connection, CancellationToken cancellationToken = default);
    Task<CapabilityResult> ExecuteCapabilityAsync(ToolConnection connection, string capabilityName, object? parameters = null, CancellationToken cancellationToken = default);
    Task<bool> TestConnectionAsync(ToolConnection connection, CancellationToken cancellationToken = default);
}