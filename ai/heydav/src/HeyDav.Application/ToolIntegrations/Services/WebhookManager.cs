using Microsoft.Extensions.Logging;
using System.Security.Cryptography;
using System.Text;
using System.Text.Json;
using System.Net;
using HeyDav.Domain.ToolIntegrations.Entities;
using HeyDav.Domain.ToolIntegrations.Enums;
using HeyDav.Domain.ToolIntegrations.Interfaces;
using HeyDav.Domain.ToolIntegrations.ValueObjects;
using HeyDav.Application.ToolIntegrations.Interfaces;
using HeyDav.Application.ToolIntegrations.Models;

namespace HeyDav.Application.ToolIntegrations.Services;

public class WebhookManager : IWebhookManager
{
    private readonly IWebhookEndpointRepository _endpointRepository;
    private readonly IWebhookEventRepository _eventRepository;
    private readonly IToolConnectionRepository _connectionRepository;
    private readonly IWebhookSecurityValidator _securityValidator;
    private readonly IWebhookEventRouter _eventRouter;
    private readonly ILogger<WebhookManager> _logger;

    public WebhookManager(
        IWebhookEndpointRepository endpointRepository,
        IWebhookEventRepository eventRepository,
        IToolConnectionRepository connectionRepository,
        IWebhookSecurityValidator securityValidator,
        IWebhookEventRouter eventRouter,
        ILogger<WebhookManager> logger)
    {
        _endpointRepository = endpointRepository;
        _eventRepository = eventRepository;
        _connectionRepository = connectionRepository;
        _securityValidator = securityValidator;
        _eventRouter = eventRouter;
        _logger = logger;
    }

    public async Task<WebhookEndpoint> RegisterWebhookAsync(WebhookRegistrationRequest request, CancellationToken cancellationToken = default)
    {
        _logger.LogInformation("Registering webhook endpoint: {Name}", request.Name);

        // Validate connection exists
        var connection = await _connectionRepository.GetByIdAsync(request.ConnectionId, cancellationToken);
        if (connection == null)
        {
            throw new ToolIntegrationException($"Connection with ID '{request.ConnectionId}' not found");
        }

        // Check if endpoint URL is already registered
        var existingEndpoint = await _endpointRepository.GetByEndpointUrlAsync(request.EndpointUrl, cancellationToken);
        if (existingEndpoint != null)
        {
            throw new ToolIntegrationException($"Webhook endpoint URL '{request.EndpointUrl}' is already registered");
        }

        // Generate a secure secret for the webhook
        var secret = GenerateWebhookSecret();

        var endpoint = new WebhookEndpoint(
            request.ConnectionId,
            request.Name,
            request.Description,
            request.EndpointUrl,
            secret);

        if (request.SecuritySettings != null)
        {
            endpoint.UpdateSecuritySettings(request.SecuritySettings);
        }

        endpoint.UpdateSupportedEvents(request.EventTypes);
        
        // Enable all supported events by default
        foreach (var eventType in request.EventTypes)
        {
            endpoint.EnableEvent(eventType);
        }

        await _endpointRepository.AddAsync(endpoint, cancellationToken);

        _logger.LogInformation("Webhook endpoint registered successfully: {EndpointId}", endpoint.Id);

        return endpoint;
    }

    public async Task<WebhookEndpoint?> GetWebhookEndpointAsync(Guid endpointId, CancellationToken cancellationToken = default)
    {
        return await _endpointRepository.GetByIdAsync(endpointId, cancellationToken);
    }

    public async Task<IEnumerable<WebhookEndpoint>> GetWebhookEndpointsAsync(Guid? connectionId = null, CancellationToken cancellationToken = default)
    {
        if (connectionId.HasValue)
        {
            return await _endpointRepository.GetByToolConnectionIdAsync(connectionId.Value, cancellationToken);
        }

        return await _endpointRepository.ListAsync(cancellationToken);
    }

    public async Task<WebhookEndpoint> UpdateWebhookEndpointAsync(Guid endpointId, WebhookRegistrationRequest request, CancellationToken cancellationToken = default)
    {
        var endpoint = await _endpointRepository.GetByIdAsync(endpointId, cancellationToken);
        if (endpoint == null)
        {
            throw new ToolIntegrationException($"Webhook endpoint with ID '{endpointId}' not found");
        }

        _logger.LogInformation("Updating webhook endpoint: {EndpointId}", endpointId);

        endpoint.UpdateEndpointUrl(request.EndpointUrl);
        endpoint.UpdateSupportedEvents(request.EventTypes);

        if (request.SecuritySettings != null)
        {
            endpoint.UpdateSecuritySettings(request.SecuritySettings);
        }

        // Update enabled events
        var currentEnabledEvents = endpoint.EnabledEvents.ToList();
        foreach (var eventType in currentEnabledEvents)
        {
            endpoint.DisableEvent(eventType);
        }

        foreach (var eventType in request.EventTypes)
        {
            endpoint.EnableEvent(eventType);
        }

        await _endpointRepository.UpdateAsync(endpoint, cancellationToken);

        _logger.LogInformation("Webhook endpoint updated successfully: {EndpointId}", endpointId);

        return endpoint;
    }

    public async Task DeleteWebhookEndpointAsync(Guid endpointId, CancellationToken cancellationToken = default)
    {
        var endpoint = await _endpointRepository.GetByIdAsync(endpointId, cancellationToken);
        if (endpoint == null)
        {
            throw new ToolIntegrationException($"Webhook endpoint with ID '{endpointId}' not found");
        }

        _logger.LogInformation("Deleting webhook endpoint: {EndpointId}", endpointId);

        await _endpointRepository.DeleteAsync(endpointId, cancellationToken);

        _logger.LogInformation("Webhook endpoint deleted successfully: {EndpointId}", endpointId);
    }

    public async Task<WebhookEndpoint> EnableWebhookEndpointAsync(Guid endpointId, CancellationToken cancellationToken = default)
    {
        var endpoint = await _endpointRepository.GetByIdAsync(endpointId, cancellationToken);
        if (endpoint == null)
        {
            throw new ToolIntegrationException($"Webhook endpoint with ID '{endpointId}' not found");
        }

        endpoint.Enable();
        await _endpointRepository.UpdateAsync(endpoint, cancellationToken);

        _logger.LogInformation("Webhook endpoint enabled: {EndpointId}", endpointId);

        return endpoint;
    }

    public async Task<WebhookEndpoint> DisableWebhookEndpointAsync(Guid endpointId, CancellationToken cancellationToken = default)
    {
        var endpoint = await _endpointRepository.GetByIdAsync(endpointId, cancellationToken);
        if (endpoint == null)
        {
            throw new ToolIntegrationException($"Webhook endpoint with ID '{endpointId}' not found");
        }

        endpoint.Disable();
        await _endpointRepository.UpdateAsync(endpoint, cancellationToken);

        _logger.LogInformation("Webhook endpoint disabled: {EndpointId}", endpointId);

        return endpoint;
    }

    public async Task<WebhookEvent> ProcessWebhookEventAsync(ProcessWebhookRequest request, CancellationToken cancellationToken = default)
    {
        _logger.LogInformation("Processing webhook event: {EventType} for endpoint {EndpointId}", request.EventType, request.WebhookEndpointId);

        var endpoint = await _endpointRepository.GetByIdAsync(request.WebhookEndpointId, cancellationToken);
        if (endpoint == null)
        {
            throw new ToolIntegrationException($"Webhook endpoint with ID '{request.WebhookEndpointId}' not found");
        }

        // Check if endpoint should process this event type
        if (!await ShouldProcessEventAsync(request.WebhookEndpointId, request.EventType, cancellationToken))
        {
            throw new WebhookValidationException("EVENT_NOT_ENABLED", $"Event type '{request.EventType}' is not enabled for this endpoint");
        }

        // Validate the webhook request
        var isValidRequest = await _securityValidator.ValidateRequestAsync(request, endpoint);
        if (!isValidRequest)
        {
            throw new WebhookValidationException("SECURITY_VALIDATION_FAILED", "Webhook security validation failed");
        }

        // Create webhook event
        var webhookEvent = new WebhookEvent(
            request.WebhookEndpointId,
            request.EventType,
            request.EventId,
            request.Payload,
            request.Headers != null ? JsonSerializer.Serialize(request.Headers) : null,
            request.Signature);

        await _eventRepository.AddAsync(webhookEvent, cancellationToken);

        // Process the event asynchronously
        _ = Task.Run(async () =>
        {
            try
            {
                await ProcessEventInternalAsync(webhookEvent, cancellationToken);
            }
            catch (Exception ex)
            {
                _logger.LogError(ex, "Failed to process webhook event {EventId}", webhookEvent.Id);
            }
        }, cancellationToken);

        // Update endpoint statistics
        endpoint.RecordEventReceived(request.EventType, true);
        await _endpointRepository.UpdateAsync(endpoint, cancellationToken);

        _logger.LogInformation("Webhook event created and queued for processing: {EventId}", webhookEvent.Id);

        return webhookEvent;
    }

    public async Task<IEnumerable<WebhookEvent>> GetWebhookEventsAsync(Guid? endpointId = null, int take = 100, CancellationToken cancellationToken = default)
    {
        if (endpointId.HasValue)
        {
            var events = await _eventRepository.GetByWebhookEndpointIdAsync(endpointId.Value, cancellationToken);
            return events.OrderByDescending(e => e.ReceivedAt).Take(take);
        }

        var allEvents = await _eventRepository.ListAsync(cancellationToken);
        return allEvents.OrderByDescending(e => e.ReceivedAt).Take(take);
    }

    public async Task<WebhookEvent?> GetWebhookEventAsync(Guid eventId, CancellationToken cancellationToken = default)
    {
        return await _eventRepository.GetByIdAsync(eventId, cancellationToken);
    }

    public async Task RetryFailedEventAsync(Guid eventId, CancellationToken cancellationToken = default)
    {
        var webhookEvent = await _eventRepository.GetByIdAsync(eventId, cancellationToken);
        if (webhookEvent == null)
        {
            throw new ToolIntegrationException($"Webhook event with ID '{eventId}' not found");
        }

        if (webhookEvent.Status != WebhookEventStatus.Failed)
        {
            throw new ToolIntegrationException($"Cannot retry event that is not in failed status. Current status: {webhookEvent.Status}");
        }

        _logger.LogInformation("Retrying failed webhook event: {EventId}", eventId);

        var nextRetryAt = CalculateNextRetryTime(webhookEvent.RetryCount);
        webhookEvent.ScheduleRetry(nextRetryAt);

        await _eventRepository.UpdateAsync(webhookEvent, cancellationToken);

        // Process the retry
        _ = Task.Run(async () =>
        {
            await Task.Delay(nextRetryAt - DateTime.UtcNow, cancellationToken);
            try
            {
                await ProcessEventInternalAsync(webhookEvent, cancellationToken);
            }
            catch (Exception ex)
            {
                _logger.LogError(ex, "Failed to retry webhook event {EventId}", eventId);
            }
        }, cancellationToken);

        _logger.LogInformation("Webhook event scheduled for retry: {EventId}", eventId);
    }

    public async Task<IEnumerable<WebhookEvent>> GetFailedEventsAsync(CancellationToken cancellationToken = default)
    {
        return await _eventRepository.GetByStatusAsync(WebhookEventStatus.Failed, cancellationToken);
    }

    public async Task<bool> ValidateWebhookSignatureAsync(string payload, string signature, string secret, string algorithm = "sha256", CancellationToken cancellationToken = default)
    {
        return await _securityValidator.ValidateSignatureAsync(payload, signature, secret, algorithm);
    }

    public async Task<bool> ValidateWebhookTimestampAsync(Dictionary<string, string> headers, int toleranceSeconds = 300, CancellationToken cancellationToken = default)
    {
        return await _securityValidator.ValidateTimestampAsync(headers, toleranceSeconds);
    }

    public async Task<bool> ValidateWebhookSourceAsync(string ipAddress, List<string> allowedRanges, CancellationToken cancellationToken = default)
    {
        return await _securityValidator.ValidateSourceIpAsync(ipAddress, allowedRanges);
    }

    public async Task<bool> ShouldProcessEventAsync(Guid endpointId, string eventType, CancellationToken cancellationToken = default)
    {
        var endpoint = await _endpointRepository.GetByIdAsync(endpointId, cancellationToken);
        return endpoint?.IsEventEnabled(eventType) ?? false;
    }

    public async Task<IEnumerable<Guid>> GetEndpointsForEventAsync(string eventType, Guid? connectionId = null, CancellationToken cancellationToken = default)
    {
        var endpoints = await _endpointRepository.GetEndpointsByEventTypeAsync(eventType, cancellationToken);
        
        if (connectionId.HasValue)
        {
            endpoints = endpoints.Where(e => e.ToolConnectionId == connectionId.Value);
        }

        return endpoints.Where(e => e.IsEventEnabled(eventType)).Select(e => e.Id);
    }

    public async Task<WebhookStatistics> GetWebhookStatisticsAsync(Guid? endpointId = null, DateTime? fromDate = null, CancellationToken cancellationToken = default)
    {
        var events = endpointId.HasValue
            ? await _eventRepository.GetByWebhookEndpointIdAsync(endpointId.Value, cancellationToken)
            : await _eventRepository.ListAsync(cancellationToken);

        if (fromDate.HasValue)
        {
            events = events.Where(e => e.ReceivedAt >= fromDate.Value);
        }

        var eventsList = events.ToList();
        var totalEvents = eventsList.Count;
        var successfulEvents = eventsList.Count(e => e.WasSuccessful);
        var failedEvents = totalEvents - successfulEvents;
        var successRate = totalEvents > 0 ? (double)successfulEvents / totalEvents : 1.0;

        var eventTypeDistribution = eventsList
            .GroupBy(e => e.EventType)
            .ToDictionary(g => g.Key, g => g.Count());

        string? endpointName = null;
        if (endpointId.HasValue)
        {
            var endpoint = await _endpointRepository.GetByIdAsync(endpointId.Value, cancellationToken);
            endpointName = endpoint?.Name;
        }

        return new WebhookStatistics(
            EndpointId: endpointId,
            EndpointName: endpointName,
            TotalEvents: totalEvents,
            SuccessfulEvents: successfulEvents,
            FailedEvents: failedEvents,
            SuccessRate: successRate,
            LastEventReceived: eventsList.MaxBy(e => e.ReceivedAt)?.ReceivedAt,
            LastSuccessfulEvent: eventsList.Where(e => e.WasSuccessful).MaxBy(e => e.ReceivedAt)?.ReceivedAt,
            EventTypeDistribution: eventTypeDistribution,
            CustomMetrics: new Dictionary<string, object>
            {
                ["AverageProcessingTime"] = eventsList.Where(e => e.ProcessingDuration.HasValue).Select(e => e.ProcessingDuration!.Value.TotalMilliseconds).DefaultIfEmpty(0).Average(),
                ["PendingEvents"] = eventsList.Count(e => e.Status == WebhookEventStatus.Pending),
                ["ProcessingEvents"] = eventsList.Count(e => e.Status == WebhookEventStatus.Processing)
            });
    }

    public async Task<IEnumerable<WebhookEndpoint>> GetUnhealthyEndpointsAsync(CancellationToken cancellationToken = default)
    {
        var endpoints = await _endpointRepository.ListAsync(cancellationToken);
        return endpoints.Where(e => !e.IsHealthy());
    }

    public async Task CleanupOldEventsAsync(TimeSpan maxAge, CancellationToken cancellationToken = default)
    {
        _logger.LogInformation("Starting cleanup of webhook events older than {MaxAge}", maxAge);

        await _eventRepository.DeleteOldEventsAsync(maxAge, cancellationToken);

        _logger.LogInformation("Webhook event cleanup completed");
    }

    private async Task ProcessEventInternalAsync(WebhookEvent webhookEvent, CancellationToken cancellationToken)
    {
        webhookEvent.MarkAsProcessing();
        await _eventRepository.UpdateAsync(webhookEvent, cancellationToken);

        try
        {
            await _eventRouter.RouteEventAsync(webhookEvent, cancellationToken);
            
            webhookEvent.MarkAsProcessed(true, triggeredWorkflows: "workflow1,workflow2"); // Placeholder
            
            // Update endpoint statistics
            var endpoint = await _endpointRepository.GetByIdAsync(webhookEvent.WebhookEndpointId, cancellationToken);
            endpoint?.RecordEventReceived(webhookEvent.EventType, true);
            if (endpoint != null)
            {
                await _endpointRepository.UpdateAsync(endpoint, cancellationToken);
            }
        }
        catch (Exception ex)
        {
            webhookEvent.MarkAsFailed($"Processing failed: {ex.Message}");
            
            // Update endpoint statistics
            var endpoint = await _endpointRepository.GetByIdAsync(webhookEvent.WebhookEndpointId, cancellationToken);
            endpoint?.RecordEventReceived(webhookEvent.EventType, false, ex.Message);
            if (endpoint != null)
            {
                await _endpointRepository.UpdateAsync(endpoint, cancellationToken);
            }

            // Schedule retry if appropriate
            if (webhookEvent.RetryCount < 3) // Max retries
            {
                var nextRetryAt = CalculateNextRetryTime(webhookEvent.RetryCount);
                webhookEvent.ScheduleRetry(nextRetryAt);
            }
            
            throw;
        }
        finally
        {
            await _eventRepository.UpdateAsync(webhookEvent, cancellationToken);
        }
    }

    private static string GenerateWebhookSecret()
    {
        const int secretLength = 32;
        var randomBytes = new byte[secretLength];
        using (var rng = RandomNumberGenerator.Create())
        {
            rng.GetBytes(randomBytes);
        }
        return Convert.ToBase64String(randomBytes);
    }

    private static DateTime CalculateNextRetryTime(int retryCount)
    {
        // Exponential backoff: 2^retryCount minutes
        var backoffMinutes = Math.Pow(2, retryCount);
        return DateTime.UtcNow.AddMinutes(backoffMinutes);
    }
}