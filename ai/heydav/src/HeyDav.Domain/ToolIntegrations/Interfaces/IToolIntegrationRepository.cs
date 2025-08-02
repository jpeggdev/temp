using HeyDav.Domain.Common.Interfaces;
using HeyDav.Domain.ToolIntegrations.Entities;
using HeyDav.Domain.ToolIntegrations.Enums;

namespace HeyDav.Domain.ToolIntegrations.Interfaces;

public interface IToolConnectionRepository : IRepository<ToolConnection>
{
    Task<ToolConnection?> GetByNameAsync(string name, CancellationToken cancellationToken = default);
    Task<IEnumerable<ToolConnection>> GetByTypeAsync(ToolType toolType, CancellationToken cancellationToken = default);
    Task<IEnumerable<ToolConnection>> GetByStatusAsync(ConnectionStatus status, CancellationToken cancellationToken = default);
    Task<IEnumerable<ToolConnection>> GetHealthyConnectionsAsync(CancellationToken cancellationToken = default);
    Task<IEnumerable<ToolConnection>> GetConnectionsNeedingHealthCheckAsync(CancellationToken cancellationToken = default);
    Task<IEnumerable<ToolConnection>> GetEnabledConnectionsAsync(CancellationToken cancellationToken = default);
}

public interface IToolCapabilityRepository : IRepository<ToolCapability>
{
    Task<IEnumerable<ToolCapability>> GetByToolConnectionIdAsync(Guid toolConnectionId, CancellationToken cancellationToken = default);
    Task<IEnumerable<ToolCapability>> GetByTypeAsync(CapabilityType type, CancellationToken cancellationToken = default);
    Task<ToolCapability?> GetByNameAndConnectionAsync(string name, Guid toolConnectionId, CancellationToken cancellationToken = default);
    Task<IEnumerable<ToolCapability>> GetEnabledCapabilitiesAsync(Guid toolConnectionId, CancellationToken cancellationToken = default);
    Task<IEnumerable<ToolCapability>> GetCapabilitiesWithUsageAsync(CancellationToken cancellationToken = default);
}

public interface IWebhookEndpointRepository : IRepository<WebhookEndpoint>
{
    Task<IEnumerable<WebhookEndpoint>> GetByToolConnectionIdAsync(Guid toolConnectionId, CancellationToken cancellationToken = default);
    Task<WebhookEndpoint?> GetByEndpointUrlAsync(string endpointUrl, CancellationToken cancellationToken = default);
    Task<IEnumerable<WebhookEndpoint>> GetByStatusAsync(WebhookStatus status, CancellationToken cancellationToken = default);
    Task<IEnumerable<WebhookEndpoint>> GetActiveEndpointsAsync(CancellationToken cancellationToken = default);
    Task<IEnumerable<WebhookEndpoint>> GetEndpointsByEventTypeAsync(string eventType, CancellationToken cancellationToken = default);
}

public interface IWebhookEventRepository : IRepository<WebhookEvent>
{
    Task<IEnumerable<WebhookEvent>> GetByWebhookEndpointIdAsync(Guid webhookEndpointId, CancellationToken cancellationToken = default);
    Task<IEnumerable<WebhookEvent>> GetByStatusAsync(WebhookEventStatus status, CancellationToken cancellationToken = default);
    Task<IEnumerable<WebhookEvent>> GetPendingEventsAsync(CancellationToken cancellationToken = default);
    Task<IEnumerable<WebhookEvent>> GetFailedEventsForRetryAsync(CancellationToken cancellationToken = default);
    Task<IEnumerable<WebhookEvent>> GetEventsByTypeAsync(string eventType, CancellationToken cancellationToken = default);
    Task<IEnumerable<WebhookEvent>> GetRecentEventsAsync(TimeSpan timeSpan, CancellationToken cancellationToken = default);
    Task<WebhookEvent?> GetByEventIdAsync(string eventId, CancellationToken cancellationToken = default);
    Task<int> GetPendingEventCountAsync(Guid webhookEndpointId, CancellationToken cancellationToken = default);
    Task DeleteOldEventsAsync(TimeSpan maxAge, CancellationToken cancellationToken = default);
}

public interface IToolSyncConfigurationRepository : IRepository<ToolSyncConfiguration>
{
    Task<IEnumerable<ToolSyncConfiguration>> GetByToolConnectionIdAsync(Guid toolConnectionId, CancellationToken cancellationToken = default);
    Task<IEnumerable<ToolSyncConfiguration>> GetEnabledConfigurationsAsync(CancellationToken cancellationToken = default);
    Task<IEnumerable<ToolSyncConfiguration>> GetConfigurationsReadyForSyncAsync(CancellationToken cancellationToken = default);
    Task<IEnumerable<ToolSyncConfiguration>> GetByEntityTypeAsync(string entityType, CancellationToken cancellationToken = default);
    Task<ToolSyncConfiguration?> GetByNameAsync(string name, CancellationToken cancellationToken = default);
}

public interface ISyncExecutionLogRepository : IRepository<SyncExecutionLog>
{
    Task<IEnumerable<SyncExecutionLog>> GetBySyncConfigurationIdAsync(Guid syncConfigurationId, CancellationToken cancellationToken = default);
    Task<IEnumerable<SyncExecutionLog>> GetRunningExecutionsAsync(CancellationToken cancellationToken = default);
    Task<IEnumerable<SyncExecutionLog>> GetRecentExecutionsAsync(TimeSpan timeSpan, CancellationToken cancellationToken = default);
    Task<SyncExecutionLog?> GetLatestExecutionAsync(Guid syncConfigurationId, CancellationToken cancellationToken = default);
    Task<IEnumerable<SyncExecutionLog>> GetFailedExecutionsAsync(CancellationToken cancellationToken = default);
    Task DeleteOldLogsAsync(TimeSpan maxAge, CancellationToken cancellationToken = default);
}