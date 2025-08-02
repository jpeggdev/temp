using HeyDav.Domain.ToolIntegrations.Entities;
using HeyDav.Application.ToolIntegrations.Models;

namespace HeyDav.Application.ToolIntegrations.Interfaces;

public interface IWebhookManager
{
    // Endpoint Management
    Task<WebhookEndpoint> RegisterWebhookAsync(WebhookRegistrationRequest request, CancellationToken cancellationToken = default);
    Task<WebhookEndpoint?> GetWebhookEndpointAsync(Guid endpointId, CancellationToken cancellationToken = default);
    Task<IEnumerable<WebhookEndpoint>> GetWebhookEndpointsAsync(Guid? connectionId = null, CancellationToken cancellationToken = default);
    Task<WebhookEndpoint> UpdateWebhookEndpointAsync(Guid endpointId, WebhookRegistrationRequest request, CancellationToken cancellationToken = default);
    Task DeleteWebhookEndpointAsync(Guid endpointId, CancellationToken cancellationToken = default);
    Task<WebhookEndpoint> EnableWebhookEndpointAsync(Guid endpointId, CancellationToken cancellationToken = default);
    Task<WebhookEndpoint> DisableWebhookEndpointAsync(Guid endpointId, CancellationToken cancellationToken = default);

    // Event Processing
    Task<WebhookEvent> ProcessWebhookEventAsync(ProcessWebhookRequest request, CancellationToken cancellationToken = default);
    Task<IEnumerable<WebhookEvent>> GetWebhookEventsAsync(Guid? endpointId = null, int take = 100, CancellationToken cancellationToken = default);
    Task<WebhookEvent?> GetWebhookEventAsync(Guid eventId, CancellationToken cancellationToken = default);
    Task RetryFailedEventAsync(Guid eventId, CancellationToken cancellationToken = default);
    Task<IEnumerable<WebhookEvent>> GetFailedEventsAsync(CancellationToken cancellationToken = default);

    // Security and Validation
    Task<bool> ValidateWebhookSignatureAsync(string payload, string signature, string secret, string algorithm = "sha256", CancellationToken cancellationToken = default);
    Task<bool> ValidateWebhookTimestampAsync(Dictionary<string, string> headers, int toleranceSeconds = 300, CancellationToken cancellationToken = default);
    Task<bool> ValidateWebhookSourceAsync(string ipAddress, List<string> allowedRanges, CancellationToken cancellationToken = default);

    // Event Filtering and Routing
    Task<bool> ShouldProcessEventAsync(Guid endpointId, string eventType, CancellationToken cancellationToken = default);
    Task<IEnumerable<Guid>> GetEndpointsForEventAsync(string eventType, Guid? connectionId = null, CancellationToken cancellationToken = default);

    // Statistics and Monitoring
    Task<WebhookStatistics> GetWebhookStatisticsAsync(Guid? endpointId = null, DateTime? fromDate = null, CancellationToken cancellationToken = default);
    Task<IEnumerable<WebhookEndpoint>> GetUnhealthyEndpointsAsync(CancellationToken cancellationToken = default);

    // Cleanup
    Task CleanupOldEventsAsync(TimeSpan maxAge, CancellationToken cancellationToken = default);
}

public interface IWebhookEventProcessor
{
    Task<bool> ProcessEventAsync(WebhookEvent webhookEvent, CancellationToken cancellationToken = default);
    Task<bool> CanProcessEventTypeAsync(string eventType, CancellationToken cancellationToken = default);
    string[] SupportedEventTypes { get; }
}

public interface IWebhookSecurityValidator
{
    Task<bool> ValidateSignatureAsync(string payload, string signature, string secret, string algorithm = "sha256");
    Task<bool> ValidateTimestampAsync(Dictionary<string, string> headers, int toleranceSeconds = 300);
    Task<bool> ValidateSourceIpAsync(string ipAddress, List<string> allowedRanges);
    Task<bool> ValidateRequestAsync(ProcessWebhookRequest request, WebhookEndpoint endpoint);
}

public interface IWebhookEventRouter
{
    Task<IEnumerable<IWebhookEventProcessor>> GetProcessorsForEventAsync(string eventType);
    Task RouteEventAsync(WebhookEvent webhookEvent, CancellationToken cancellationToken = default);
    Task RegisterProcessorAsync(IWebhookEventProcessor processor);
    Task UnregisterProcessorAsync(IWebhookEventProcessor processor);
}